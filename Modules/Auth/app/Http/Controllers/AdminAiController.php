<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AdminAiService;

class AdminAiController extends Controller
{
    public function __construct(
        private readonly AdminAiService $adminAiService
    ) {}

    /**
     * Handle incoming admin prompt and return AI copilot response with live ERP context.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        /** @var array<int, array{role: string, content: string}> $history */
        $history = session()->get('admin_ai_chat_history', []);

        $history[] = [
            'role' => 'user',
            'content' => $validated['message'],
        ];

        // Maintain the most recent 12 messages for conversation continuity
        if (count($history) > 12) {
            $history = array_slice($history, -12);
        }

        $reply = $this->adminAiService->chat($history, $request->user());

        $history[] = $reply;
        session()->put('admin_ai_chat_history', $history);

        return response()->json([
            'status' => 'success',
            'reply' => $reply['content'] ?? 'Operational overview complete. How else can I assist?',
        ]);
    }

    /**
     * Reset the admin conversation memory stored in the session.
     */
    public function clearHistory(): JsonResponse
    {
        session()->forget('admin_ai_chat_history');

        return response()->json([
            'status' => 'cleared',
            'message' => 'Admin chat history cleared successfully.',
        ]);
    }
}
