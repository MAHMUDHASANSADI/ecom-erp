<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\Setting;
use Modules\Catalog\Models\Product;
use Modules\Finance\Models\Expense;
use Modules\POS\Models\Sale;
use Modules\Storefront\Models\Order;

class AdminAiService
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
     * Process incoming admin message thread with live ERP context.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{role: string, content: string}
     */
    public function chat(array $messages, ?User $user = null): array
    {
        $this->apiKey = trim((string) config('services.ai_chat.api_key', $this->apiKey));
        $this->model = (string) config('services.ai_chat.model', $this->model);
        $this->baseUrl = rtrim((string) config('services.ai_chat.base_url', $this->baseUrl), '/');
        $this->provider = (string) config('services.ai_chat.provider', $this->provider);

        // If no API key is configured, fallback to direct local ERP calculations
        if ($this->apiKey === '') {
            return $this->handleLocalFallback($messages, $user);
        }

        try {
            $systemPrompt = $this->buildSystemPrompt($user);

            // 1. Google Gemini Native API
            if ($this->provider === 'gemini' || str_contains($this->baseUrl, 'generativelanguage.googleapis.com')) {
                $geminiReply = $this->callGeminiNative($messages, $systemPrompt);
                if ($geminiReply !== null) {
                    return ['role' => 'assistant', 'content' => $geminiReply];
                }
            }

            // 2. OpenAI-Compatible endpoint (Groq, OpenAI, etc.)
            $openAiReply = $this->callOpenAiCompatible($messages, $systemPrompt);
            if ($openAiReply !== null) {
                return ['role' => 'assistant', 'content' => $openAiReply];
            }

            return $this->handleLocalFallback($messages, $user);
        } catch (\Throwable $e) {
            Log::error('Admin AI Copilot Exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $this->handleLocalFallback($messages, $user);
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
                'temperature' => 0.4, // Lower temperature for more factual operational queries
                'maxOutputTokens' => 1200,
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
                Log::warning("Admin Gemini Native API ({$modelName}) failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }

        return null;
    }

    /**
     * Call OpenAI-compatible endpoint.
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
            'temperature' => 0.4,
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout(20)
            ->post($endpoint, $payload);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        Log::warning('Admin AI OpenAI-compatible endpoint failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Construct the full system instruction with live ERP data snapshots.
     */
    private function buildSystemPrompt(?User $user = null): string
    {
        $appName = rescue(
            fn () => Setting::getValue('app_name', config('app.name', 'Our Store')),
            config('app.name', 'Our Store'),
            false
        );

        $erpContext = $this->gatherErpContext($user);

        return "You are the intelligent ERP Operations Assistant and Business Analyst for '{$appName}'.
You assist store administrators, managers, and warehouse supervisors with real-time analytics, inventory health, order management, and operations.

OPERATIONAL PRINCIPLES:
1. Deliver concise, actionable answers with bold key metrics.
2. Structure data with clear bullet points or compact tables when summarizing metrics.
3. Provide direct Markdown links to admin pages when relevant (e.g. [Adjust Inventory](/admin/inventory/adjust), [View Orders](/admin/orders), [POS Terminal](/admin/pos)).
4. Only report verified metrics from the LIVE ERP SNAPSHOT below. Never invent revenue or inventory numbers.

LIVE ERP SNAPSHOT (REAL-TIME DATA):
{$erpContext}

COMMON ADMIN SHORTCUTS:
- Point of Sale: [Open POS Terminal](/admin/pos)
- Catalog & Products: [Manage Products](/admin/products) | [Create Product](/admin/products/create)
- Inventory: [Inventory Overview](/admin/inventory) | [Stock Adjustment](/admin/inventory/adjust)
- Orders: [View Orders](/admin/orders)
- Finance: [Expense Tracker](/admin/finance/expenses) | [Sales Report](/admin/finance/sales) | [Profit Report](/admin/finance/profit)";
    }

    /**
     * Aggregate real-time ERP snapshots from Database models.
     */
    public function gatherErpContext(?User $user = null): string
    {
        $sections = [];

        // 1. Sales & Revenue Snapshot
        try {
            $todaySalesCount = Sale::whereDate('created_at', today())->where('status', 'completed')->count();
            $todaySalesSum = (float) Sale::whereDate('created_at', today())->where('status', 'completed')->sum('total');

            $monthSalesCount = Sale::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status', 'completed')->count();
            $monthSalesSum = (float) Sale::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status', 'completed')->sum('total');

            // Channel Breakdown (POS vs Storefront)
            $posTodaySum = (float) Sale::whereDate('created_at', today())->where('status', 'completed')->where('channel', 'pos')->sum('total');
            $onlineTodaySum = (float) Sale::whereDate('created_at', today())->where('status', 'completed')->where('channel', 'online')->sum('total');

            $sections[] = "--- SALES METRICS ---\n"
                ."- Today's Revenue: $".number_format($todaySalesSum, 2)." ({$todaySalesCount} completed sales)\n"
                .'  * POS In-Store: $'.number_format($posTodaySum, 2)."\n"
                .'  * Online Storefront: $'.number_format($onlineTodaySum, 2)."\n"
                .'- This Month Revenue ('.now()->format('F Y').'): $'.number_format($monthSalesSum, 2)." ({$monthSalesCount} sales)";
        } catch (\Throwable $e) {
            $sections[] = '--- SALES METRICS ---\nSales data temporarily unavailable.';
        }

        // 2. Inventory Health Snapshot
        try {
            $activeProducts = Product::where('is_active', true)->get();
            $totalActive = $activeProducts->count();

            $lowStockItems = [];
            $outOfStockItems = [];

            foreach ($activeProducts as $product) {
                $stock = $product->current_stock;
                if ($stock <= 0) {
                    $outOfStockItems[] = "{$product->name} (SKU: {$product->sku}, Stock: 0)";
                } elseif ($stock <= 5) {
                    $lowStockItems[] = "{$product->name} (SKU: {$product->sku}, Stock: {$stock})";
                }
            }

            $lowStockText = empty($lowStockItems) ? 'None (Healthy)' : implode(', ', array_slice($lowStockItems, 0, 8));
            $outStockText = empty($outOfStockItems) ? 'None' : implode(', ', array_slice($outOfStockItems, 0, 8));

            $sections[] = "--- INVENTORY HEALTH ---\n"
                ."- Active Products: {$totalActive}\n"
                .'- Out of Stock Count: '.count($outOfStockItems)." [{$outStockText}]\n"
                .'- Low Stock Count (<= 5 units): '.count($lowStockItems)." [{$lowStockText}]";
        } catch (\Throwable $e) {
            $sections[] = '--- INVENTORY HEALTH ---\nInventory data temporarily unavailable.';
        }

        // 3. Orders Backlog
        try {
            $pendingOrdersCount = Order::where('status', 'pending')->count();
            $processingOrdersCount = Order::where('status', 'processing')->count();
            $deliveredOrdersCount = Order::where('status', 'delivered')->count();

            $recentPending = Order::where('status', 'pending')->latest()->take(5)->get()->map(function ($order) {
                return "#{$order->id} ({$order->customer_name}, Payment: {$order->payment_method} - {$order->payment_status})";
            })->implode(', ');

            $sections[] = "--- ORDERS BACKLOG ---\n"
                ."- Pending Orders (Needs Attention): {$pendingOrdersCount}\n"
                .'  * Recent Pending: '.($recentPending ?: 'No pending orders')."\n"
                ."- Processing Orders: {$processingOrdersCount}\n"
                ."- Delivered Orders: {$deliveredOrdersCount}";
        } catch (\Throwable $e) {
            $sections[] = '--- ORDERS BACKLOG ---\nOrder data temporarily unavailable.';
        }

        // 4. Financial Expense Overview (Optional Permission Guard)
        $canViewFinance = ! $user || $user->hasPermissionTo('view_finance_reports') || $user->hasRole('Owner');
        if ($canViewFinance) {
            try {
                $monthExpenses = (float) Expense::inMonth(now()->year, now()->month)->sum('amount');
                $topExpenseCategories = Expense::inMonth(now()->year, now()->month)
                    ->selectRaw('category, sum(amount) as total')
                    ->groupBy('category')
                    ->orderByDesc('total')
                    ->take(3)
                    ->get()
                    ->map(fn ($exp) => "{$exp->category}: $".number_format((float) $exp->total, 2))
                    ->implode(', ');

                $sections[] = '--- FINANCIAL OVERVIEW ('.now()->format('F Y').") ---\n"
                    .'- Total Month Expenses: $'.number_format($monthExpenses, 2)."\n"
                    .'- Top Expense Breakdown: '.($topExpenseCategories ?: 'No expenses logged this month');
            } catch (\Throwable $e) {
                // Ignore if finance tables are missing or empty
            }
        }

        return implode("\n\n", $sections);
    }

    /**
     * Smart local fallback to answer operational questions if API key is unset or offline.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{role: string, content: string}
     */
    private function handleLocalFallback(array $messages, ?User $user = null): array
    {
        $lastMessage = end($messages);
        $userText = strtolower(trim($lastMessage['content'] ?? ''));

        // Low stock query
        if (str_contains($userText, 'stock') || str_contains($userText, 'restock') || str_contains($userText, 'inventory')) {
            try {
                $lowStock = [];
                foreach (Product::where('is_active', true)->get() as $p) {
                    if ($p->current_stock <= 5) {
                        $lowStock[] = "- **{$p->name}** (SKU: `{$p->sku}`) — **{$p->current_stock} units left**";
                    }
                }

                if (! empty($lowStock)) {
                    $reply = "⚠️ **Low Stock Alert (5 units or fewer):**\n\n".implode("\n", array_slice($lowStock, 0, 10))."\n\n👉 [Adjust Inventory Stock](/admin/inventory/adjust)";

                    return ['role' => 'assistant', 'content' => $reply];
                }

                return ['role' => 'assistant', 'content' => '✅ **Inventory is Healthy!** No active products are currently below the low stock threshold (<= 5 units).'];
            } catch (\Throwable) {
                // Ignore
            }
        }

        // Sales query
        if (str_contains($userText, 'sale') || str_contains($userText, 'revenue') || str_contains($userText, 'today')) {
            try {
                $todayCount = Sale::whereDate('created_at', today())->where('status', 'completed')->count();
                $todaySum = (float) Sale::whereDate('created_at', today())->where('status', 'completed')->sum('total');
                $monthSum = (float) Sale::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status', 'completed')->sum('total');

                $reply = "📊 **Sales Performance Summary:**\n\n"
                    ."- **Today's Revenue:** $".number_format($todaySum, 2)." ({$todayCount} completed transactions)\n"
                    ."- **This Month's Total:** $".number_format($monthSum, 2)."\n\n"
                    .'👉 [Open Sales Report](/admin/finance/sales) | [Open POS Terminal](/admin/pos)';

                return ['role' => 'assistant', 'content' => $reply];
            } catch (\Throwable) {
                // Ignore
            }
        }

        // Pending orders query
        if (str_contains($userText, 'order') || str_contains($userText, 'pending')) {
            try {
                $pending = Order::where('status', 'pending')->take(5)->get();
                $count = Order::where('status', 'pending')->count();

                if ($count > 0) {
                    $lines = [];
                    foreach ($pending as $o) {
                        $lines[] = "- [**Order #{$o->id}**](/admin/orders/{$o->id}) — {$o->customer_name} ({$o->payment_method})";
                    }

                    $reply = "📦 **Pending Orders Backlog ({$count} pending):**\n\n".implode("\n", $lines)."\n\n👉 [Manage All Orders](/admin/orders)";

                    return ['role' => 'assistant', 'content' => $reply];
                }

                return ['role' => 'assistant', 'content' => '✅ **All Caught Up!** There are currently 0 pending orders awaiting fulfillment.'];
            } catch (\Throwable) {
                // Ignore
            }
        }

        // Expenses query
        if (str_contains($userText, 'expense') || str_contains($userText, 'finance') || str_contains($userText, 'cost')) {
            try {
                $monthExpenses = (float) Expense::inMonth(now()->year, now()->month)->sum('amount');
                $reply = '💰 **Monthly Expenses Overview ('.now()->format('F Y')."):**\n\n"
                    .'- **Total Logged Expenses:** $'.number_format($monthExpenses, 2)."\n\n"
                    .'👉 [View Expense Tracker](/admin/finance/expenses) | [Profit & Loss Report](/admin/finance/profit)';

                return ['role' => 'assistant', 'content' => $reply];
            } catch (\Throwable) {
                // Ignore
            }
        }

        // Default greeting / overview
        return [
            'role' => 'assistant',
            'content' => "👋 **Hello, Admin!** I am your ERP Operations Copilot.\n\nAsk me about:\n- 📊 *\"Today's sales summary\"*\n- ⚠️ *\"Which products are low on stock?\"*\n- 📦 *\"Show pending orders\"*\n- 💰 *\"Monthly expense breakdown\"*",
        ];
    }
}
