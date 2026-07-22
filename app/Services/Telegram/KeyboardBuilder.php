<?php

namespace App\Services\Telegram;

use Illuminate\Support\Collection;

class KeyboardBuilder
{
    // Label Konstanta Tombol Menu Pembeli
    public const BTN_HOME           = '🏠 Home';
    public const BTN_PRODUCTS       = '🛍️ Produk';
    public const BTN_MY_ORDERS      = '📦 Pesanan Saya';
    public const BTN_PAYMENT_INFO   = '💳 Cara Pembayaran';
    public const BTN_CONTACT_ADMIN  = '☎️ Hubungi Admin';
    public const BTN_HELP          = '❓ Bantuan';

    // Label Tombol Navigasi Kembali & Batal
    public const BTN_BACK_MAIN     = '🔙 Kembali ke Menu Utama';
    public const BTN_BACK_CATEGORY = '🔙 Kembali ke Kategori';
    public const BTN_BACK_PRODUCT  = '🔙 Kembali ke Produk';
    public const BTN_CANCEL_ORDER  = '❌ Batalkan Pesanan';

    /**
     * Menu Utama (ReplyKeyboard).
     */
    public static function mainMenu(): array
    {
        return [
            'keyboard' => [
                [['text' => self::BTN_HOME], ['text' => self::BTN_PRODUCTS]],
                [['text' => self::BTN_MY_ORDERS], ['text' => self::BTN_PAYMENT_INFO]],
                [['text' => self::BTN_CONTACT_ADMIN], ['text' => self::BTN_HELP]],
            ],
            'resize_keyboard'   => true,
            'one_time_keyboard' => false,
        ];
    }

    /**
     * Keyboard Daftar Kategori.
     */
    public static function categories(Collection $categories): array
    {
        $keyboard = [];
        foreach ($categories as $cat) {
            $icon = $cat->icon ?: '📁';
            $keyboard[] = [['text' => "{$icon} {$cat->name}"]];
        }
        $keyboard[] = [['text' => self::BTN_BACK_MAIN]];

        return [
            'keyboard'          => $keyboard,
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Keyboard Daftar Produk.
     */
    public static function products(Collection $products): array
    {
        $keyboard = [];
        foreach ($products as $prod) {
            $keyboard[] = [['text' => "🎁 {$prod->name}"]];
        }
        $keyboard[] = [['text' => self::BTN_BACK_CATEGORY]];

        return [
            'keyboard'          => $keyboard,
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Keyboard Daftar Varian Produk.
     */
    public static function productVariants(Collection $variants): array
    {
        $keyboard = [];
        foreach ($variants as $var) {
            $priceFormatted = "Rp" . number_format($var->price, 0, ',', '.');
            $keyboard[] = [['text' => "⚡ {$var->name} - {$priceFormatted}"]];
        }
        $keyboard[] = [['text' => self::BTN_BACK_PRODUCT]];

        return [
            'keyboard'          => $keyboard,
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Keyboard Batal dalam alur konfirmasi pesanan/bukti bayar.
     */
    public static function cancelOrder(string $btnText = '🔙 Batal'): array
    {
        return [
            'keyboard'          => [[['text' => $btnText]]],
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Inline Keyboard Notifikasi Verifikasi Admin.
     */
    public static function adminVerification(int $orderId): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Verifikasi', 'callback_data' => "verify_order_{$orderId}"],
                    ['text' => '❌ Tolak', 'callback_data' => "reject_order_{$orderId}"],
                ]
            ]
        ];
    }
}
