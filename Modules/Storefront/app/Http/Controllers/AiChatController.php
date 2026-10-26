<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Storefront\Services\AiChatService;

class AiChatController extends Controller
{
    public function __construct(
        private readonly AiChatService $aiChatService
    ) {}

    /**
     * Handle incoming user message and return the AI assistant's reply.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        /** @var array<int, array{role: string, content: string}> $history */
        $history = session()->get('shop_chat_history', []);

        // Add user message
        $history[] = [
            'role' => 'user',
            'content' => $validated['message'],
        ];

        // Maintain the most recent 10 messages for conversation continuity
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        $reply = $this->aiChatService->chat($history);

        // Store assistant response in history
        $history[] = $reply;
        session()->put('shop_chat_history', $history);

        return response()->json([
            'status' => 'success',
            'reply' => $reply['content'] ?? 'How else can I help you?',
        ]);
    }

    /**
     * Clear the conversation history from the current user's session.
     */
    public function clearHistory(): JsonResponse
    {
        session()->forget('shop_chat_history');

        return response()->json([
            'status' => 'cleared',
            'message' => 'Conversation history reset.',
        ]);
    }
}
