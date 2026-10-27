<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAiTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_admin_ai(): void
    {
        $response = $this->postJson(route('admin.ai.chat'), [
            'message' => 'Show me low stock',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_admin_can_send_message_and_receive_response(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin User', 'email' => 'admin@example.com', 'is_active' => true]);
        $this->actingAs($user);

        $response = $this->postJson(route('admin.ai.chat'), [
            'message' => 'Today sales',
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

    public function test_admin_ai_message_validation(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin User', 'email' => 'admin@example.com', 'is_active' => true]);
        $this->actingAs($user);

        $response = $this->postJson(route('admin.ai.chat'), [
            'message' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_admin_can_clear_ai_conversation_history(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin User', 'email' => 'admin@example.com', 'is_active' => true]);
        $this->actingAs($user);

        $this->withSession(['admin_ai_chat_history' => [['role' => 'user', 'content' => 'Overview']]]);

        $response = $this->postJson(route('admin.ai.clear'));

        $response->assertOk()
            ->assertJson([
                'status' => 'cleared',
            ]);

        $this->assertNull(session('admin_ai_chat_history'));
    }

    public function test_admin_ai_with_mocked_gemini_provider(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin User', 'email' => 'admin@example.com', 'is_active' => true]);
        $this->actingAs($user);

        config(['services.ai_chat.api_key' => 'test-fake-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Here is your sales performance report for today.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('admin.ai.chat'), [
            'message' => 'Sales overview',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'reply' => 'Here is your sales performance report for today.',
            ]);
    }
}
