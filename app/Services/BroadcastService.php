<?php

namespace App\Services;

use App\Jobs\BroadcastMessage;
use App\Models\TelegramUser;

class BroadcastService
{
    /**
     * Kirim broadcast ke semua atau sebagian user.
     *
     * @param string $message Isi pesan
     * @param string $target Target ('all' atau 'active')
     * @return int Jumlah penerima
     */
    public function send(string $message, string $target = 'all'): int
    {
        $query = TelegramUser::where('is_blocked', false);

        if ($target === 'active') {
            // Hanya user yang pernah order
            $query->whereHas('orders');
        }

        $users = $query->get();

        foreach ($users as $user) {
            BroadcastMessage::dispatch($user->telegram_id, $message);
        }

        return $users->count();
    }
}
