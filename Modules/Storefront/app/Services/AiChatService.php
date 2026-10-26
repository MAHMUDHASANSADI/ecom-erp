<?php

namespace Modules\Storefront\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\Setting;
use Modules\Catalog\Models\Product;
use Modules\Storefront\Models\Order;

class AiChatService
{
    private string $provider;
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->provider = (string) config('services.ai_chat.provider', 'gemini');
        $this->apiKey = trim((string) config('services.ai_chat.api_key', ''));
        $this->model = (string) config('services.ai_chat.model', 'gemini-3.8-flash');
        $this->baseUrl = rtrim((string) config('services.ai_chat.base_url', 'https://generativelanguage.googleapis.com/v1beta/openai/'), '/');
    }

    /**
     * Process incoming message thread and return the AI assistant's response.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{role: string, content: string}
     */
    public function chat(array $messages): array
    {
        $this->apiKey = trim((string) config('services.ai_chat.api_key', $this->apiKey));
        $this->model = (string) config('services.ai_chat.model', $this->model);
        $this->baseUrl = rtrim((string) config('services.ai_chat.base_url', $this->baseUrl), '/');
        $this->provider = (string) config('services.ai_chat.provider', $this->provider);

        // If no API key is configured, fallback to local search
        if ($this->apiKey === '') {
            return $this->handleLocalFallback($messages);
        }

        try {
            $systemPrompt = $this->buildSystemPrompt($messages);

            // 1. If provider is Gemini or using Google Generative Language API
            if ($this->provider === 'gemini' || str_contains($this->baseUrl, 'generativelanguage.googleapis.com')) {
                $geminiReply = $this->callGeminiNative($messages, $systemPrompt);
                if ($geminiReply !== null) {
                    return ['role' => 'assistant', 'content' => $geminiReply];
                }
            }

            // 2. Try OpenAI-compatible endpoint (Groq, OpenAI, or Gemini OpenAI compatibility)
            $openAiReply = $this->callOpenAiCompatible($messages, $systemPrompt);
            if ($openAiReply !== null) {
                return ['role' => 'assistant', 'content' => $openAiReply];
            }

            // If remote APIs failed, fallback gracefully to database search
            return $this->handleLocalFallback($messages);
        } catch (\Throwable $e) {
            Log::error('AI Chatbot Exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $this->handleLocalFallback($messages);
        }
    }

    /**
     * Call Google Gemini API using native generateContent endpoint.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    private function callGeminiNative(array $messages, string $systemPrompt): ?string
    {
        $candidateModels = array_unique([$this->model, 'gemini-3.8-flash', 'gemini-3.7-flash', 'gemini-2.0-flash']);

        // Convert messages to Gemini contents structure
        $contents = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'assistant' || $msg['role'] === 'model') ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $msg['content'] ?? ''],
                ],
            ];
        }

        // If first message in contents is not user, prepend user prompt
        if (! empty($contents) && $contents[0]['role'] === 'model') {
            array_unshift($contents, [
                'role' => 'user',
                'parts' => [['text' => 'Hello']],
            ]);
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
            ],
        ];

        foreach ($candidateModels as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(20)->post($url, $payload);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                if (! empty($text)) {
                    return $text;
                }
            } else {
                Log::warning("Gemini Native API ({$modelName}) failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }

        return null;
    }

    /**
     * Call OpenAI-compatible chat completions endpoint (OpenAI, Groq, etc.).
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    private function callOpenAiCompatible(array $messages, string $systemPrompt): ?string
    {
        $endpoint = str_ends_with($this->baseUrl, 'chat/completions')
            ? $this->baseUrl
            : $this->baseUrl.'/chat/completions';

        $formattedMessages = array_merge([
            ['role' => 'system', 'content' => $systemPrompt],
        ], $messages);

        $payload = [
            'model' => $this->model,
            'messages' => $formattedMessages,
            'temperature' => 0.7,
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout(20)
            ->post($endpoint, $payload);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        Log::warning('AI Chat OpenAI-compatible endpoint failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Build system prompt with real store inventory and live context.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    private function buildSystemPrompt(array $messages): string
    {
        $appName = rescue(
            fn () => Setting::getValue('app_name', config('app.name', 'Our Store')),
            config('app.name', 'Our Store'),
            false
        );

        // Fetch active catalog products to ground the AI in real items
        $catalogList = '';
        try {
            $products = Product::where('is_active', true)
                ->with('category')
                ->take(25)
                ->get();

            if ($products->isNotEmpty()) {
                $catalogLines = [];
                foreach ($products as $p) {
                    $catName = $p->category?->name ?? 'General';
                    $stock = $p->current_stock > 0 ? "In Stock ({$p->current_stock} units)" : 'Out of Stock';
                    $price = '$'.number_format((float) $p->price, 2);
                    $url = route('storefront.products.show', $p->id);
                    $catalogLines[] = "- [{$p->name}]({$url}) | Price: {$price} | Category: {$catName} | Status: {$stock}";
                }
                $catalogList = implode("\n", $catalogLines);
            }
        } catch (\Throwable) {
            $catalogList = '';
        }

        // Check if user is asking to track an order
        $orderContext = '';
        $lastMessage = end($messages);
        $userText = strtolower((string) ($lastMessage['content'] ?? ''));

        if (preg_match('/(?:order|track(?:ing)?)\s*#?\s*(\d+)/i', $userText, $matches)) {
            $orderId = (int) $matches[1];
            try {
                $order = Order::find($orderId);
                if ($order) {
                    $orderContext = "\nLIVE ORDER LOOKUP FOR ORDER #{$order->id}:\n"
                        ."- Status: ".ucfirst($order->status)."\n"
                        ."- Customer: {$order->customer_name}\n"
                        ."- Payment: ".strtoupper($order->payment_method).' ('.ucfirst($order->payment_status).")\n"
                        .'- Date: '.($order->created_at?->format('M d, Y h:i A') ?? 'Recent');
                } else {
                    $orderContext = "\nLIVE ORDER LOOKUP: Order #{$orderId} was not found in our database.";
                }
            } catch (\Throwable) {
                // Ignore DB error
            }
        }

        return "You are a warm, courteous, and highly knowledgeable AI Shopping Assistant for '{$appName}'.
Your goals:
1. Help visitors discover and buy products from our catalog.
2. Provide direct product links using markdown: [Product Name](url) with price and stock status.
3. Help customers track orders accurately.
4. Answer shipping, payment (COD & SSLCommerz), and return policy questions.

CURRENT STORE INVENTORY:
{$catalogList}
{$orderContext}

STORE POLICIES:
- Shipping: Standard 2-4 business days nationwide delivery.
- Payment Methods: Cash on Delivery (COD) and Online Payments via SSLCommerz.
- Returns: 7-day hassle-free returns on unused items.

GUIDELINES:
- Be concise, engaging, and friendly.
- Always recommend real products from the catalog above with their link and price.
- If a customer asks about an order number, refer to the verified LIVE ORDER LOOKUP data.";
    }

    /**
     * Smart local fallback when no API key is provided or remote API fails.
     * Searches database directly and provides helpful answers.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{role: string, content: string}
     */
    private function handleLocalFallback(array $messages): array
    {
        $lastMessage = end($messages);
        $userText = strtolower(trim($lastMessage['content'] ?? ''));

        // Check for order tracking intent
        if (preg_match('/(?:order|track(?:ing)?)\s*#?\s*(\d+)/i', $userText, $matches)) {
            $orderId = (int) $matches[1];
            try {
                $order = Order::find($orderId);
                if ($order) {
                    return [
                        'role' => 'assistant',
                        'content' => "📦 **Order #{$order->id} Status:**\n- **Status:** ".ucfirst($order->status)."\n- **Customer:** {$order->customer_name}\n- **Payment Method:** ".strtoupper($order->payment_method).' ('.ucfirst($order->payment_status).")\n- **Date:** ".($order->created_at?->format('M d, Y') ?? 'Recent')."\n\nLet me know if you need anything else!",
                    ];
                }
            } catch (\Throwable) {
                // Ignore
            }

            return [
                'role' => 'assistant',
                'content' => "I could not find Order #{$orderId}. Please check the order number or contact support.",
            ];
        }

        // Check for shipping/payment intent
        if (str_contains($userText, 'shipping') || str_contains($userText, 'delivery')) {
            return [
                'role' => 'assistant',
                'content' => "🚚 **Delivery Information:**\nWe offer fast nationwide delivery within 2-4 business days. Express shipping is also available at checkout!",
            ];
        }

        if (str_contains($userText, 'payment') || str_contains($userText, 'pay') || str_contains($userText, 'cod')) {
            return [
                'role' => 'assistant',
                'content' => "💳 **Payment Methods:**\nWe accept **Cash on Delivery (COD)** as well as secure online payments via **SSLCommerz** (Debit/Credit Card, Mobile Banking).",
            ];
        }

        // Catalog search intent
        try {
            $queryWords = preg_replace('/[^\w\s]/', '', $userText);
            $keywords = array_filter(explode(' ', (string) $queryWords), fn ($w) => strlen($w) > 2);

            $dbQuery = Product::query()->where('is_active', true);

            if (! empty($keywords)) {
                $dbQuery->where(function ($q) use ($keywords): void {
                    foreach ($keywords as $word) {
                        $q->orWhere('name', 'like', "%{$word}%")
                            ->orWhere('description', 'like', "%{$word}%");
                    }
                });
            }

            $products = $dbQuery->take(3)->get();

            if ($products->isNotEmpty()) {
                $response = "Here are matching products from our shop:\n\n";
                foreach ($products as $p) {
                    $url = route('storefront.products.show', $p->id);
                    $stockStatus = $p->current_stock > 0 ? '✅ In Stock' : '⚠️ Out of Stock';
                    $price = '$'.number_format((float) $p->price, 2);
                    $response .= "- [**{$p->name}**]({$url}) — **{$price}** ({$stockStatus})\n";
                }
                $response .= "\nClick on any product to view details and add it to your cart!";

                return ['role' => 'assistant', 'content' => $response];
            }
        } catch (\Throwable) {
            // Ignore DB error
        }

        return [
            'role' => 'assistant',
            'content' => "👋 Hi there! I'm your AI Shopping Assistant.\n\nYou can ask me to:\n- 🔍 **Find products** (e.g. *\"Show iPhones\"* or *\"Laptops\"*)\n- 📦 **Track an order** (e.g. *\"Track order #1\"*)\n- 🚚 **Ask about shipping & payment**\n\nHow can I help you today?",
        ];
    }
}
