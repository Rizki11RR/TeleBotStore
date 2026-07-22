<?php

namespace App\Services\Telegram;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TelegramSession;
use App\Models\TelegramUser;

class ProductHandler
{
    public function __construct(
        private readonly MessageSender $sender
    ) {}

    /**
     * Kirim daftar kategori aktif ke user.
     */
    public function sendCategories(TelegramUser $user): void
    {
        $categories = Category::active()->ordered()->get();

        if ($categories->isEmpty()) {
            $this->sender->text(
                $user->telegram_id,
                "Mohon maaf, belum ada kategori produk yang tersedia saat ini."
            );
            $this->sender->text(
                $user->telegram_id,
                "Silakan pilih menu utama:",
                KeyboardBuilder::mainMenu()
            );
            return;
        }

        $this->sender->text(
            $user->telegram_id,
            "Silakan pilih kategori produk:",
            KeyboardBuilder::categories($categories)
        );
    }

    /**
     * Tangani pilihan kategori dari user.
     */
    public function handleChooseCategory(TelegramUser $user, TelegramSession $session, string $text): void
    {
        if ($text === KeyboardBuilder::BTN_BACK_MAIN) {
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sender->text(
                $user->telegram_id,
                "Kembali ke menu utama:",
                KeyboardBuilder::mainMenu()
            );
            return;
        }

        // Pencocokan persis tombol atau pencocokan nama bersih
        $categories = Category::active()->get();
        $category = $categories->first(function ($cat) use ($text) {
            $icon = $cat->icon ?: '📁';
            return $text === "{$icon} {$cat->name}"
                || $text === $cat->name
                || trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $text)) === $cat->name;
        });

        if (!$category) {
            $this->sender->text(
                $user->telegram_id,
                "Pilihan kategori tidak valid. Silakan pilih dari menu tombol."
            );
            $this->sendCategories($user);
            return;
        }

        $session->update([
            'state' => 'CHOOSE_PRODUCT',
            'data'  => ['category_id' => $category->id]
        ]);

        $this->sendProducts($user, $category);
    }

    /**
     * Kirim daftar produk berdasarkan kategori.
     */
    public function sendProducts(TelegramUser $user, Category $category): void
    {
        $products = Product::where('category_id', $category->id)->active()->ordered()->get();

        if ($products->isEmpty()) {
            $this->sender->text(
                $user->telegram_id,
                "Belum ada produk di kategori *{$category->name}*.",
                null,
                'Markdown'
            );
            $this->sendCategories($user);
            return;
        }

        $this->sender->text(
            $user->telegram_id,
            "Pilih produk dari kategori *{$category->name}*:",
            KeyboardBuilder::products($products),
            'Markdown'
        );
    }

    /**
     * Tangani pilihan produk dari user.
     */
    public function handleChooseProduct(TelegramUser $user, TelegramSession $session, string $text): void
    {
        if ($text === KeyboardBuilder::BTN_BACK_CATEGORY) {
            $session->update(['state' => 'CHOOSE_CATEGORY', 'data' => []]);
            $this->sendCategories($user);
            return;
        }

        $categoryId = $session->data['category_id'] ?? null;
        $products = Product::where('category_id', $categoryId)->active()->get();
        $product = $products->first(function ($prod) use ($text) {
            return $text === "🎁 {$prod->name}"
                || $text === $prod->name
                || trim(preg_replace('/^🎁\s*/u', '', $text)) === $prod->name;
        });

        if (!$product) {
            $this->sender->text(
                $user->telegram_id,
                "Pilihan produk tidak valid."
            );
            if ($categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    $this->sendProducts($user, $category);
                    return;
                }
            }
            $this->sendCategories($user);
            return;
        }

        $session->update([
            'state' => 'CHOOSE_VARIANT',
            'data'  => array_merge($session->data ?? [], ['product_id' => $product->id])
        ]);

        $this->sendProductDetails($user, $product);
    }

    /**
     * Kirim detail produk & pilihan varian.
     */
    public function sendProductDetails(TelegramUser $user, Product $product): void
    {
        $product->load(['variants' => function ($q) {
            $q->active();
        }]);

        if ($product->variants->isEmpty()) {
            $this->sender->text(
                $user->telegram_id,
                "Mohon maaf, produk <b>{$product->name}</b> belum memiliki varian aktif.",
                null,
                'HTML'
            );
            $this->sendCategories($user);
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

        $this->sender->text(
            $user->telegram_id,
            $text,
            KeyboardBuilder::productVariants($product->variants),
            'HTML'
        );
    }

    /**
     * Tangani pilihan varian produk dari user.
     */
    public function handleChooseVariant(TelegramUser $user, TelegramSession $session, string $text): void
    {
        $productId = $session->data['product_id'] ?? null;
        $product = Product::find($productId);

        if ($text === KeyboardBuilder::BTN_BACK_PRODUCT) {
            if ($product) {
                $session->update([
                    'state' => 'CHOOSE_PRODUCT',
                    'data'  => ['category_id' => $product->category_id]
                ]);
                $this->sendProducts($user, $product->category);
            } else {
                $session->update(['state' => 'CHOOSE_CATEGORY', 'data' => []]);
                $this->sendCategories($user);
            }
            return;
        }

        // Parse: "⚡ Varian - Rp50.000"
        $cleanText = preg_replace('/^⚡\s*/u', '', $text);
        $parts = explode(' - Rp', $cleanText);
        $variantName = trim($parts[0]);

        $variant = ProductVariant::where('name', $variantName)->where('product_id', $productId)->active()->first();

        if (!$variant) {
            $this->sender->text(
                $user->telegram_id,
                "Pilihan varian tidak valid."
            );
            if ($product) {
                $this->sendProductDetails($user, $product);
            }
            return;
        }

        if ($variant->stock == 0) {
            $this->sender->text(
                $user->telegram_id,
                "Mohon maaf, varian produk ini sedang habis."
            );
            return;
        }

        $session->update([
            'state' => 'CONFIRM_ORDER',
            'data'  => array_merge($session->data ?? [], ['variant_id' => $variant->id])
        ]);

        $this->sender->text(
            $user->telegram_id,
            "✍️ Silakan masukkan *Catatan Pesanan* atau data yang dibutuhkan (misal: Username Instagram, Link Profile, Nomor HP, atau Email Anda):",
            KeyboardBuilder::cancelOrder('🔙 Batal'),
            'Markdown'
        );
    }
}
