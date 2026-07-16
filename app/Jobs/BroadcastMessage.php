<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class BroadcastMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal sebelum job gagal.
     */
    public int $tries = 3;

    /**
     * Backoff/delay percobaan ulang dalam detik.
     */
    public int $backoff = 10;

    public function __construct(
        private readonly int $chatId,
        private readonly string $message
    ) {}

    public function handle(): void
    {
        try {
            Telegram::sendMessage([
                'chat_id'    => $this->chatId,
                'text'       => $this->message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal mengirim broadcast ke {$this->chatId}: " . $e->getMessage());
            // Jika error adalah block, kita bisa tandai is_blocked = true di database
            // Namun di Job ini kita biarkan lempar error untuk retry atau biarkan fail
            throw $e;
        }
    }
}
