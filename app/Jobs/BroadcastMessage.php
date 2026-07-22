<?php

namespace App\Jobs;

use App\Models\TelegramUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class BroadcastMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal sebelum job gagal.
     * Untuk error "chat not found" tidak perlu retry, jadi di-handle manual.
     */
    public int $tries = 1;

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
        } catch (TelegramResponseException $e) {
            $errorMsg = strtolower($e->getMessage());

            // Jika user tidak ditemukan atau memblokir bot, tandai sebagai blocked
            // agar tidak dikirim lagi di broadcast berikutnya
            $isUnreachable = str_contains($errorMsg, 'chat not found')
                || str_contains($errorMsg, 'bot was blocked by the user')
                || str_contains($errorMsg, 'user is deactivated')
                || str_contains($errorMsg, 'have no rights to send');

            if ($isUnreachable) {
                TelegramUser::where('telegram_id', $this->chatId)
                    ->update(['is_blocked' => true]);

                Log::info("Broadcast: user {$this->chatId} ditandai blocked ({$e->getMessage()})");
                return; // Skip, jangan throw — broadcast ke user lain harus tetap jalan
            }

            // Error lain (rate limit, server error): log dan biarkan gagal
            Log::error("Broadcast gagal ke {$this->chatId}: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error("Broadcast error tidak dikenal ke {$this->chatId}: " . $e->getMessage());
            throw $e;
        }
    }
}
