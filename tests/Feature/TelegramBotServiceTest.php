<?php

namespace Tests\Feature;

use App\Models\TelegramSession;
use App\Models\TelegramUser;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Telegram\Bot\Objects\Update;
use Tests\TestCase;

class TelegramBotServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_update_creates_user_and_session(): void
    {
        $updateData = [
            'update_id' => 12345678,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 999888777,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                ],
                'chat' => [
                    'id' => 999888777,
                    'type' => 'private',
                ],
                'date' => time(),
                'text' => '/start',
            ],
        ];

        $update = new Update($updateData);

        /** @var TelegramBotService $service */
        $service = app(TelegramBotService::class);
        $service->handleUpdate($update);

        $this->assertDatabaseHas('telegram_users', [
            'telegram_id' => 999888777,
            'first_name'  => 'John',
            'username'    => 'johndoe',
        ]);

        $user = TelegramUser::where('telegram_id', 999888777)->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('telegram_sessions', [
            'telegram_user_id' => $user->id,
            'state'            => 'MENU',
        ]);
    }
}
