<?php

namespace App\Services\Telegram;

use App\Models\TelegramSession;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class TelegramBotService
{
    public function __construct(
        private readonly CommandRouter $commandRouter,
        private readonly CallbackRouter $callbackRouter
    ) {}

    /**
     * Menangani update webhook Telegram.
     */
    public function handleUpdate(Update $update): void
    {
        // 1. Tangani CallbackQuery (Tombol Inline Keyboard Admin)
        $callbackQuery = $update->getCallbackQuery();
        if ($callbackQuery) {
            $this->callbackRouter->handle($callbackQuery);
            return;
        }

        // 2. Tangani Message
        $message = $update->getMessage();
        if (!$message) {
            return;
        }

        $from = $message->getFrom();
        if (!$from) {
            return;
        }

        // Upsert Telegram User (Null-safe first_name)
        $user = TelegramUser::updateOrCreate(
            ['telegram_id' => (string)$from->getId()],
            [
                'username'   => $from->getUsername(),
                'first_name' => $from->getFirstName() ?: ($from->getUsername() ?: 'User'),
                'last_name'  => $from->getLastName(),
            ]
        );

        if ($user->is_blocked) {
            return;
        }

        // Dapatkan atau buat sesi
        $session = TelegramSession::firstOrCreate(
            ['telegram_user_id' => $user->id],
            ['state' => 'MENU', 'data' => []]
        );

        // 3. Teruskan ke CommandRouter
        $this->commandRouter->handle($user, $session, $message);
    }
}
