<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Telegram\Bot\Laravel\Facades\Telegram;

class WebhookController extends Controller
{
    public function __construct(private readonly TelegramBotService $botService) {}

    /**
     * Handle incoming Telegram webhook.
     * Return 200 cepat ke Telegram, proses di background via Queue.
     */
    public function handle(Request $request): Response
    {
        $update = Telegram::commandsHandler(true);

        if ($update) {
            $this->botService->handleUpdate($update);
        }

        return response('OK', 200);
    }
}
