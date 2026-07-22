<?php

namespace App\Services\Telegram;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\CallbackQuery;

class PaymentHandler
{
    public function __construct(
        private readonly MessageSender $sender,
        private readonly DeliveryService $deliveryService,
        private readonly ActivityLogService $logService
    ) {}

    /**
     * Verifikasi pembayaran oleh admin.
     */
    public function verify(CallbackQuery $callbackQuery, int $orderId): bool
    {
        $from = $callbackQuery->getFrom();
        $fromTelegramId = $from->getId();
        $adminUsername = $from->getUsername() ? "@" . $from->getUsername() : ($from->getFirstName() ?? 'Admin');
        $message = $callbackQuery->getMessage();

        // 1. Keamanan: Cek Otorisasi Admin
        $adminTelegramId = Setting::get('admin_telegram_id');
        $isAdmin = ($adminTelegramId && (string)$adminTelegramId === (string)$fromTelegramId);

        if (!$isAdmin) {
            $this->sender->answerCallback(
                $callbackQuery->getId(),
                '⚠️ Anda tidak memiliki akses untuk melakukan tindakan ini.',
                true
            );
            return false;
        }

        $order = Order::with(['payment', 'telegramUser', 'productVariant.product'])->find($orderId);

        if (!$order || !$order->payment) {
            $this->sender->answerCallback(
                $callbackQuery->getId(),
                '❌ Pesanan atau data pembayaran tidak ditemukan.',
                true
            );
            return false;
        }

        // 2. Audit & Idempotensi: Cek jika status sudah diproses
        if ($order->status !== OrderStatus::WAITING_VERIFICATION || $order->payment->status !== PaymentStatus::WAITING) {
            $statusLabel = $order->status->label();
            $this->sender->answerCallback(
                $callbackQuery->getId(),
                "⚠️ Pesanan {$order->invoice_number} sudah diproses sebelumnya (Status: {$statusLabel}).",
                true
            );

            // Hapus tombol keyboard agar tidak bisa diklik lagi
            if ($message) {
                $this->sender->editReplyMarkup($message->getChat()->getId(), $message->getMessageId(), ['inline_keyboard' => []]);
            }
            return false;
        }

        $admin = Admin::first();
        $payment = $order->payment;
        $productName = $order->productVariant->product->name ?? 'Produk';
        $variantName = $order->productVariant->name ?? 'Varian';
        $buyerTelegramId = $order->telegramUser->telegram_id;

        // UPDATE PAYMENT & ORDER
        $payment->update([
            'status'      => PaymentStatus::VERIFIED,
            'verified_at' => now(),
            'verified_by' => $admin?->id,
        ]);

        $order->update([
            'status' => OrderStatus::PAID,
        ]);

        // SERAHKAN KE DELIVERY SERVICE UNTUK PENGIRIMAN OTOMATIS
        $this->deliveryService->deliver($order);

        // NOTIFIKASI KE PEMBELI
        $this->sender->text(
            $buyerTelegramId,
            "✅ *PEMBAYARAN DIVERIFIKASI*\n\nPembayaran untuk invoice `{$order->invoice_number}` telah diverifikasi oleh admin. Terima kasih!\n\nProduk Anda telah/sedang dikirimkan di atas.",
            null,
            'Markdown'
        );

        // LOG AKTIVITAS AUDIT
        $this->logService->log(
            'payment.verify_telegram',
            "Memverifikasi pembayaran via Telegram Admin untuk order {$order->invoice_number}",
            $payment,
            $admin?->id
        );

        // EDIT PESAN ADMIN TELEGRAM
        $updatedCaption = "✅ *PESANAN TELAH DIVERIFIKASI*\n\n" .
                          "Invoice: `{$order->invoice_number}`\n" .
                          "Produk: *{$productName} - {$variantName}*\n" .
                          "Nominal: *Rp" . number_format($order->total_price, 0, ',', '.') . "*\n" .
                          "Pembeli: *{$order->telegramUser->full_name}*\n" .
                          "Verifikator: *{$adminUsername}*\n" .
                          "Waktu: " . now()->format('d/m/Y H:i:s');

        if ($message) {
            $hasPhoto = !empty($message->getPhoto());
            if ($hasPhoto) {
                $this->sender->editCaption(
                    $message->getChat()->getId(),
                    $message->getMessageId(),
                    $updatedCaption,
                    ['inline_keyboard' => []],
                    'Markdown'
                );
            } else {
                $this->sender->editText(
                    $message->getChat()->getId(),
                    $message->getMessageId(),
                    $updatedCaption,
                    ['inline_keyboard' => []],
                    'Markdown'
                );
            }
        }

        $this->sender->answerCallback(
            $callbackQuery->getId(),
            '✅ Pesanan berhasil diverifikasi!',
            false
        );

        return true;
    }

    /**
     * Penolakan pembayaran oleh admin.
     */
    public function reject(CallbackQuery $callbackQuery, int $orderId): bool
    {
        $from = $callbackQuery->getFrom();
        $fromTelegramId = $from->getId();
        $adminUsername = $from->getUsername() ? "@" . $from->getUsername() : ($from->getFirstName() ?? 'Admin');
        $message = $callbackQuery->getMessage();

        // 1. Keamanan: Cek Otorisasi Admin
        $adminTelegramId = Setting::get('admin_telegram_id');
        $isAdmin = ($adminTelegramId && (string)$adminTelegramId === (string)$fromTelegramId);

        if (!$isAdmin) {
            $this->sender->answerCallback(
                $callbackQuery->getId(),
                '⚠️ Anda tidak memiliki akses untuk melakukan tindakan ini.',
                true
            );
            return false;
        }

        $order = Order::with(['payment', 'telegramUser', 'productVariant.product'])->find($orderId);

        if (!$order || !$order->payment) {
            $this->sender->answerCallback(
                $callbackQuery->getId(),
                '❌ Pesanan atau data pembayaran tidak ditemukan.',
                true
            );
            return false;
        }

        if ($order->status !== OrderStatus::WAITING_VERIFICATION || $order->payment->status !== PaymentStatus::WAITING) {
            $statusLabel = $order->status->label();
            $this->sender->answerCallback(
                $callbackQuery->getId(),
                "⚠️ Pesanan {$order->invoice_number} sudah diproses sebelumnya (Status: {$statusLabel}).",
                true
            );

            if ($message) {
                $this->sender->editReplyMarkup($message->getChat()->getId(), $message->getMessageId(), ['inline_keyboard' => []]);
            }
            return false;
        }

        $admin = Admin::first();
        $payment = $order->payment;
        $productName = $order->productVariant->product->name ?? 'Produk';
        $variantName = $order->productVariant->name ?? 'Varian';
        $buyerTelegramId = $order->telegramUser->telegram_id;

        // REJECT ORDER
        $payment->update([
            'status'           => PaymentStatus::REJECTED,
            'rejection_reason' => "Ditolak via Telegram Admin oleh {$adminUsername}",
            'verified_at'      => now(),
            'verified_by'      => $admin?->id,
        ]);

        $order->update([
            'status' => OrderStatus::WAITING_PAYMENT,
        ]);

        // NOTIFIKASI KE PEMBELI
        $this->sender->text(
            $buyerTelegramId,
            "❌ *PEMBAYARAN DITOLAK*\n\nPembayaran untuk invoice `{$order->invoice_number}` ditolak oleh admin.\n\nSilakan periksa kembali bukti transfer Anda dan kirimkan ulang bukti pembayaran yang valid.",
            null,
            'Markdown'
        );

        // LOG AKTIVITAS AUDIT
        $this->logService->log(
            'payment.reject_telegram',
            "Menolak pembayaran via Telegram Admin untuk order {$order->invoice_number}",
            $payment,
            $admin?->id
        );

        // EDIT PESAN ADMIN TELEGRAM
        $updatedCaption = "❌ *PESANAN TELAH DITOLAK*\n\n" .
                          "Invoice: `{$order->invoice_number}`\n" .
                          "Produk: *{$productName} - {$variantName}*\n" .
                          "Nominal: *Rp" . number_format($order->total_price, 0, ',', '.') . "*\n" .
                          "Pembeli: *{$order->telegramUser->full_name}*\n" .
                          "Ditolak oleh: *{$adminUsername}*\n" .
                          "Waktu: " . now()->format('d/m/Y H:i:s');

        if ($message) {
            $hasPhoto = !empty($message->getPhoto());
            if ($hasPhoto) {
                $this->sender->editCaption(
                    $message->getChat()->getId(),
                    $message->getMessageId(),
                    $updatedCaption,
                    ['inline_keyboard' => []],
                    'Markdown'
                );
            } else {
                $this->sender->editText(
                    $message->getChat()->getId(),
                    $message->getMessageId(),
                    $updatedCaption,
                    ['inline_keyboard' => []],
                    'Markdown'
                );
            }
        }

        $this->sender->answerCallback(
            $callbackQuery->getId(),
            '❌ Pesanan telah ditolak.',
            false
        );

        return true;
    }
}
