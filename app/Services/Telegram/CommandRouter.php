<?php

namespace App\Services\Telegram;

use App\Models\Setting;
use App\Models\TelegramSession;
use App\Models\TelegramUser;

class CommandRouter
{
    public function __construct(
        private readonly MessageSender $sender,
        private readonly ProductHandler $productHandler,
        private readonly OrderHandler $orderHandler
    ) {}

    /**
     * Routing pesan masuk (Message) berdasarkan teks/perintah atau state sesi.
     */
    public function handle(TelegramUser $user, TelegramSession $session, $message): void
    {
        $text = trim($message->getText() ?? '');

        // 1. Cek perintah utama / global
        if ($text === '/start' || $text === KeyboardBuilder::BTN_HOME || $text === '🏠 Home / Menu Utama') {
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sendMenu($user);
            return;
        }

        if ($text === KeyboardBuilder::BTN_HELP) {
            $helpMsg = Setting::get('bot_help_message', 'Silakan hubungi admin jika Anda memerlukan bantuan.');
            $this->sender->text(
                $user->telegram_id,
                "❓ *BANTUAN & PANDUAN*\n\n" . $helpMsg,
                null,
                'Markdown'
            );
            return;
        }

        if ($text === KeyboardBuilder::BTN_CONTACT_ADMIN) {
            $contactMsg = Setting::get('bot_contact_admin', 'Hubungi @admin jika ada pertanyaan.');
            $this->sender->text(
                $user->telegram_id,
                "☎️ *HUBUNGI ADMIN*\n\n" . $contactMsg,
                null,
                'Markdown'
            );
            return;
        }

        if ($text === KeyboardBuilder::BTN_PAYMENT_INFO) {
            $paymentInfoText = Setting::get('bot_payment_info', 'Scan QRIS dan kirim bukti pembayaran.');
            $this->sender->text(
                $user->telegram_id,
                "💳 *CARA PEMBAYARAN*\n\n" . $paymentInfoText,
                null,
                'Markdown'
            );
            return;
        }

        if ($text === KeyboardBuilder::BTN_MY_ORDERS) {
            $this->orderHandler->handleMyOrders($user);
            return;
        }

        if ($text === KeyboardBuilder::BTN_PRODUCTS) {
            $session->update(['state' => 'CHOOSE_CATEGORY']);
            $this->productHandler->sendCategories($user);
            return;
        }

        // 2. Delegasikan ke state handler jika bukan perintah utama
        switch ($session->state) {
            case 'CHOOSE_CATEGORY':
                $this->productHandler->handleChooseCategory($user, $session, $text);
                break;
            case 'CHOOSE_PRODUCT':
                $this->productHandler->handleChooseProduct($user, $session, $text);
                break;
            case 'CHOOSE_VARIANT':
                $this->productHandler->handleChooseVariant($user, $session, $text);
                break;
            case 'CONFIRM_ORDER':
                $this->orderHandler->handleConfirmOrder($user, $session, $text);
                break;
            case 'WAITING_PAYMENT_PROOF':
                $this->orderHandler->handleWaitingPaymentProof($user, $session, $message);
                break;
            default:
                $session->update(['state' => 'MENU', 'data' => []]);
                $this->sendMenu($user);
                break;
        }
    }

    /**
     * Kirim menu utama.
     */
    public function sendMenu(TelegramUser $user): void
    {
        $storeName = Setting::get('store_name', 'Nexora Digital');
        $welcomeMsg = Setting::get('bot_welcome_message', "Halo! Selamat datang di *{$storeName}* 🛍️\n\nSilakan pilih menu:");

        $this->sender->text(
            $user->telegram_id,
            $welcomeMsg,
            KeyboardBuilder::mainMenu(),
            'Markdown'
        );
    }
}
