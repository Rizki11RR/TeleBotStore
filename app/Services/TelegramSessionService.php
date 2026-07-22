<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

class TelegramSessionService
{
    /**
     * Kirim menu utama ke user.
     */
    public function sendMenu(string|int $chatId): void
    {
        $storeName = Setting::get('store_name', 'Nexora Digital');
        $welcomeMsg = Setting::get('bot_welcome_message', "Halo! Selamat datang di *{$storeName}* 🛍️\n\nSilakan pilih menu:");

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $welcomeMsg,
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => '🏠 Home'], ['text' => '🛍️ Produk']],
                    [['text' => '📦 Pesanan Saya'], ['text' => '💳 Cara Pembayaran']],
                    [['text' => '☎️ Hubungi Admin'], ['text' => '❓ Bantuan']],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }

    /**
     * Kirim daftar kategori.
     */
    public function sendCategories(string|int $chatId): void
    {
        $categories = Category::active()->ordered()->get();

        if ($categories->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => "Mohon maaf, belum ada kategori produk yang tersedia saat ini.",
            ]);
            $this->sendMenu($chatId);
            return;
        }

        $keyboard = [];
        foreach ($categories as $cat) {
            $icon = $cat->icon ?: '📁';
            $keyboard[] = [['text' => "{$icon} {$cat->name}"]];
        }
        $keyboard[] = [['text' => '🔙 Kembali ke Menu Utama']];

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => "Silakan pilih kategori produk:",
            'reply_markup' => json_encode([
                'keyboard'          => $keyboard,
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    /**
     * Kirim daftar produk berdasarkan kategori.
     */
    public function sendProducts(string|int $chatId, Category $category): void
    {
        $products = Product::where('category_id', $category->id)->active()->ordered()->get();

        if ($products->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => "Belum ada produk di kategori *{$category->name}*.",
                'parse_mode' => 'Markdown',
            ]);
            $this->sendCategories($chatId);
            return;
        }

        $keyboard = [];
        foreach ($products as $prod) {
            $keyboard[] = [['text' => "🎁 {$prod->name}"]];
        }
        $keyboard[] = [['text' => '🔙 Kembali ke Kategori']];

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => "Pilih produk dari kategori *{$category->name}*:",
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard'          => $keyboard,
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    /**
     * Kirim detail produk & pilihan varian.
     */
    public function sendProductDetails(string|int $chatId, Product $product): void
    {
        $product->load(['variants' => function ($q) {
            $q->active();
        }]);

        if ($product->variants->isEmpty()) {
            Telegram::sendMessage([
                'chat_id'    => $chatId,
                'text'       => "Mohon maaf, produk <b>{$product->name}</b> belum memiliki varian aktif.",
                'parse_mode' => 'HTML',
            ]);
            $this->sendCategories($chatId);
            return;
        }

        $description = $product->description ?: 'Tidak ada deskripsi.';
        
        $variantLines = "";
        foreach ($product->variants as $var) {
            $priceFormatted = "Rp" . number_format($var->price, 0, ',', '.');
            if ($var->original_price && $var->original_price > $var->price) {
                $origFormatted = "<s>Rp" . number_format($var->original_price, 0, ',', '.') . "</s>";
                $variantLines .= "• <b>{$var->name}</b>: {$priceFormatted} (Promo! Harga asli: {$origFormatted})\n";
            } else {
                $variantLines .= "• <b>{$var->name}</b>: {$priceFormatted}\n";
            }
        }

        $text = "🎁 <b>{$product->name}</b>\n\n" .
                "📝 <b>Deskripsi:</b>\n{$description}\n\n" .
                "💵 <b>Daftar Varian & Harga:</b>\n{$variantLines}\n" .
                "Silakan pilih varian produk di bawah ini:";

        $keyboard = [];
        foreach ($product->variants as $var) {
            $keyboard[] = [['text' => "⚡ {$var->name} - Rp" . number_format($var->price, 0, ',', '.')]];
        }
        $keyboard[] = [['text' => '🔙 Kembali ke Produk']];

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => json_encode([
                'keyboard'          => $keyboard,
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    /**
     * Kirim invoice & QRIS untuk pembayaran.
     */
    public function sendQrisPayment(string|int $chatId, Order $order): void
    {
        $qrisImage = Setting::get('qris_image_path');
        $accountName = Setting::get('qris_account_name', 'Nexora Digital');
        $paymentInfoText = Setting::get('bot_payment_info', "Silakan scan kode QRIS di atas untuk melakukan pembayaran.");

        $invoiceDetails = "🧾 *INVOICE PEMBAYARAN*\n\n" .
                          "Nomor Invoice: `{$order->invoice_number}`\n" .
                          "Produk: *{$order->productVariant->product->name}*\n" .
                          "Varian: *{$order->productVariant->name}*\n" .
                          "Nominal: *Rp" . number_format($order->total_price, 0, ',', '.') . "*\n" .
                          "Catatan/Data: `{$order->notes}`\n\n" .
                          $paymentInfoText . "\n\n" .
                          "⚠️ *PENTING:* Setelah transfer, silakan kirimkan *FOTO BUKTI PEMBAYARAN* Anda di sini.";

        if ($qrisImage && file_exists(storage_path('app/public/' . $qrisImage))) {
            Telegram::sendPhoto([
                'chat_id' => $chatId,
                'photo'   => InputFile::create(storage_path('app/public/' . $qrisImage), 'qris.png'),
                'caption' => "Scan QRIS a/n: {$accountName}",
            ]);
        } else {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => "⚠️ *Metode QRIS belum aktif.* Harap hubungi admin.",
                'parse_mode' => 'Markdown',
            ]);
        }

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $invoiceDetails,
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard'          => [[['text' => '❌ Batalkan Pesanan']]],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }
}
