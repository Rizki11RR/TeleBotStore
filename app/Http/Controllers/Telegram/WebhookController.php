<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class WebhookController extends Controller
{
    public function __construct(private readonly TelegramBotService $botService) {}

    /**
     * Handle incoming Telegram webhook.
     * Return 200 cepat ke Telegram.
     */
    public function handle(Request $request): Response
    {
        try {
            $update = Telegram::getWebhookUpdate();

            if ($update) {
                $this->botService->handleUpdate($update);
            }
        } catch (\Throwable $e) {
            Log::error("Telegram Webhook Error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return response('OK', 200);
    }
}
