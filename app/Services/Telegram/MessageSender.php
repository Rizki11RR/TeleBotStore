<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class MessageSender
{
    /**
     * Kirim pesan teks.
     */
    public function text(int|string $chatId, string $text, ?array $replyMarkup = null, ?string $parseMode = 'Markdown'): bool
    {
        try {
            $params = [
                'chat_id' => $chatId,
                'text'    => $text,
            ];

            if ($parseMode) {
                $params['parse_mode'] = $parseMode;
            }

            if ($replyMarkup) {
                $params['reply_markup'] = json_encode($replyMarkup);
            }

            Telegram::sendMessage($params);
            return true;
        } catch (\Throwable $e) {
            Log::error("MessageSender::text error: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'text'    => $text,
            ]);

            // Try fallback without parse_mode if Markdown parsing failed
            if ($parseMode) {
                try {
                    $params['parse_mode'] = null;
                    Telegram::sendMessage($params);
                    return true;
                } catch (\Throwable $ex) {
                    Log::error("MessageSender::text fallback error: " . $ex->getMessage());
                }
            }
            return false;
        }
    }

    /**
     * Kirim foto dengan caption.
     */
    public function photo(int|string $chatId, string|InputFile $photo, ?string $caption = null, ?array $replyMarkup = null, ?string $parseMode = 'Markdown'): bool
    {
        try {
            $params = [
                'chat_id' => $chatId,
                'photo'   => $photo,
            ];

            if ($caption) {
                $params['caption'] = $caption;
            }

            if ($parseMode) {
                $params['parse_mode'] = $parseMode;
            }

            if ($replyMarkup) {
                $params['reply_markup'] = json_encode($replyMarkup);
            }

            Telegram::sendPhoto($params);
            return true;
        } catch (\Throwable $e) {
            Log::error("MessageSender::photo error: " . $e->getMessage(), [
                'chat_id' => $chatId,
            ]);
            return false;
        }
    }

    /**
     * Edit teks pesan yang sudah terkirim.
     */
    public function editText(int|string $chatId, int $messageId, string $text, ?array $replyMarkup = null, ?string $parseMode = 'Markdown'): bool
    {
        try {
            $params = [
                'chat_id'    => $chatId,
                'message_id' => $messageId,
                'text'       => $text,
            ];

            if ($parseMode) {
                $params['parse_mode'] = $parseMode;
            }

            if ($replyMarkup !== null) {
                $params['reply_markup'] = json_encode($replyMarkup);
            }

            Telegram::editMessageText($params);
            return true;
        } catch (\Throwable $e) {
            Log::error("MessageSender::editText error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Edit caption foto yang sudah terkirim.
     */
    public function editCaption(int|string $chatId, int $messageId, string $caption, ?array $replyMarkup = null, ?string $parseMode = 'Markdown'): bool
    {
        try {
            $params = [
                'chat_id'    => $chatId,
                'message_id' => $messageId,
                'caption'    => $caption,
            ];

            if ($parseMode) {
                $params['parse_mode'] = $parseMode;
            }

            if ($replyMarkup !== null) {
                $params['reply_markup'] = json_encode($replyMarkup);
            }

            Telegram::editMessageCaption($params);
            return true;
        } catch (\Throwable $e) {
            Log::error("MessageSender::editCaption error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Edit reply markup (keyboard) pesan.
     */
    public function editReplyMarkup(int|string $chatId, int $messageId, array $replyMarkup = []): bool
    {
        try {
            Telegram::editMessageReplyMarkup([
                'chat_id'      => $chatId,
                'message_id'   => $messageId,
                'reply_markup' => json_encode($replyMarkup),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error("MessageSender::editReplyMarkup error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Jawab callback query (menghentikan loading spinner di Telegram client).
     */
    public function answerCallback(string $callbackQueryId, ?string $text = null, bool $showAlert = false): bool
    {
        try {
            $params = [
                'callback_query_id' => $callbackQueryId,
                'show_alert'        => $showAlert,
            ];

            if ($text !== null) {
                $params['text'] = $text;
            }

            Telegram::answerCallbackQuery($params);
            return true;
        } catch (\Throwable $e) {
            Log::error("MessageSender::answerCallback error: " . $e->getMessage());
            return false;
        }
    }
}
