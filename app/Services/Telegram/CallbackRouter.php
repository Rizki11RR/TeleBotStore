<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\CallbackQuery;

class CallbackRouter
{
    public function __construct(
        private readonly MessageSender $sender,
        private readonly PaymentHandler $paymentHandler
    ) {}

    /**
     * Dispatch callback_query dari tombol inline Telegram.
     * Jaminan: answerCallback() selalu dipanggil agar spinner tombol Telegram tidak hanging.
     */
    public function handle(CallbackQuery $callbackQuery): void
    {
        $callbackQueryId = $callbackQuery->getId();
        $data = $callbackQuery->getData();

        $answered = false;

        try {
            if (str_starts_with($data, 'verify_order_')) {
                $orderId = (int) str_replace('verify_order_', '', $data);
                $answered = $this->paymentHandler->verify($callbackQuery, $orderId);
            } elseif (str_starts_with($data, 'reject_order_')) {
                $orderId = (int) str_replace('reject_order_', '', $data);
                $answered = $this->paymentHandler->reject($callbackQuery, $orderId);
            } else {
                Log::warning("CallbackRouter: callback_data tidak dikenali: {$data}");
                $this->sender->answerCallback($callbackQueryId, '⚠️ Aksi tidak dikenali atau sudah kedaluwarsa.', true);
                $answered = true;
            }
        } catch (\Throwable $e) {
            Log::error("CallbackRouter error: " . $e->getMessage(), [
                'callback_data' => $data,
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
            ]);

            if (!$answered) {
                $this->sender->answerCallback($callbackQueryId, '❌ Terjadi kesalahan saat memproses aksi ini.', true);
            }
        }
    }
}
