<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    public function test_user_can_send_chat_message_and_receive_fallback_response(): void
    {
        $response = $this->postJson(route('storefront.chat.message'), [
            'message' => 'Show me products',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'reply',
            ])
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_chat_message_validation(): void
    {
        $response = $this->postJson(route('storefront.chat.message'), [
            'message' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_user_can_clear_chat_history(): void
    {
        $this->withSession(['shop_chat_history' => [['role' => 'user', 'content' => 'Hello']]]);

        $response = $this->postJson(route('storefront.chat.clear'));

        $response->assertOk()
            ->assertJson([
                'status' => 'cleared',
            ]);

        $this->assertNull(session('shop_chat_history'));
    }

    public function test_chat_with_mocked_ai_provider(): void
    {
        config(['services.ai_chat.api_key' => 'test-fake-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hello! How may I assist you with your shopping today?'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('storefront.chat.message'), [
            'message' => 'Hello',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'reply' => 'Hello! How may I assist you with your shopping today?',
            ]);
    }
}
