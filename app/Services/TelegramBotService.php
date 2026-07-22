<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\TelegramSession;
use App\Models\TelegramUser;
use App\Services\ActivityLogService;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Update;

class TelegramBotService
{
    public function __construct(
        private readonly TelegramSessionService $sessionService,
        private readonly DeliveryService $deliveryService,
        private readonly ActivityLogService $logService
    ) {}

    /**
     * Menangani update webhook Telegram.
     */
    public function handleUpdate(Update $update): void
    {
        try {
            $this->processUpdate($update);
        } catch (\Throwable $e) {
            Log::error('handleUpdate fatal error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    private function processUpdate(Update $update): void
    {
        // Tangani callback query (tombol Inline Keyboard)
        $callbackQuery = $update->getCallbackQuery();
        if ($callbackQuery) {
            $this->handleCallbackQuery($callbackQuery);
            return;
        }

        $message = $update->getMessage();
        if (!$message) {
            return;
        }

        $from = $message->getFrom();
        if (!$from) {
            return;
        }

        // Upsert Telegram User
        $user = TelegramUser::updateOrCreate(
            ['telegram_id' => $from->getId()],
            [
                'username'   => $from->getUsername(),
                'first_name' => $from->getFirstName() ?: ($from->getUsername() ?: 'User'),
                'last_name'  => $from->getLastName(),
            ]
        );

        if ($user->is_blocked) {
            return;
        }

        // Dapatkan atau buat sesi
        $session = TelegramSession::firstOrCreate(
            ['telegram_user_id' => $user->id],
            ['state' => 'MENU', 'data' => []]
        );

        $text = trim($message->getText() ?? '');

        // Cek jika perintah utama
        if ($text === '/start' || $text === '🏠 Home' || $text === '🏠 Home / Menu Utama') {
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sessionService->sendMenu($user->telegram_id);
            return;
        }

        if ($text === '❓ Bantuan') {
            $helpMsg = Setting::get('bot_help_message', 'Silakan hubungi admin jika Anda memerlukan bantuan.');
            Telegram::sendMessage([
                'chat_id'    => $user->telegram_id,
                'text'       => "❓ *BANTUAN & PANDUAN*\n\n" . $helpMsg,
                'parse_mode' => 'Markdown',
            ]);
            return;
        }

        if ($text === '☎️ Hubungi Admin') {
            $contactMsg = Setting::get('bot_contact_admin', 'Hubungi @admin jika ada pertanyaan.');
            Telegram::sendMessage([
                'chat_id'    => $user->telegram_id,
                'text'       => "☎️ *HUBUNGI ADMIN*\n\n" . $contactMsg,
                'parse_mode' => 'Markdown',
            ]);
            return;
        }

        if ($text === '💳 Cara Pembayaran') {
            $paymentInfoText = Setting::get('bot_payment_info', 'Scan QRIS dan kirim bukti pembayaran.');
            Telegram::sendMessage([
                'chat_id'    => $user->telegram_id,
                'text'       => "💳 *CARA PEMBAYARAN*\n\n" . $paymentInfoText,
                'parse_mode' => 'Markdown',
            ]);
            return;
        }

        if ($text === '📦 Pesanan Saya') {
            $this->handleMyOrders($user);
            return;
        }

        if ($text === '🛍️ Produk') {
            $session->update(['state' => 'CHOOSE_CATEGORY']);
            $this->sessionService->sendCategories($user->telegram_id);
            return;
        }

        // Delegasikan ke state handler
        switch ($session->state) {
            case 'CHOOSE_CATEGORY':
                $this->handleChooseCategory($user, $session, $text);
                break;
            case 'CHOOSE_PRODUCT':
                $this->handleChooseProduct($user, $session, $text);
                break;
            case 'CHOOSE_VARIANT':
                $this->handleChooseVariant($user, $session, $text);
                break;
            case 'CONFIRM_ORDER':
                $this->handleConfirmOrder($user, $session, $text);
                break;
            case 'WAITING_PAYMENT_PROOF':
                $this->handleWaitingPaymentProof($user, $session, $message);
                break;
            default:
                $session->update(['state' => 'MENU', 'data' => []]);
                $this->sessionService->sendMenu($user->telegram_id);
                break;
        }
    } // end processUpdate

    private function handleChooseCategory(TelegramUser $user, TelegramSession $session, string $text): void
    {
        if ($text === '🔙 Kembali ke Menu Utama') {
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sessionService->sendMenu($user->telegram_id);
            return;
        }

        // Temukan kategori berdasarkan nama (pencocokan persis tombol atau nama bersih)
        $categories = Category::active()->get();
        $category = $categories->first(function ($cat) use ($text) {
            $icon = $cat->icon ?: '📁';
            return $text === "{$icon} {$cat->name}" 
                || $text === $cat->name 
                || trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $text)) === $cat->name;
        });

        if (!$category) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "Pilihan kategori tidak valid. Silakan pilih dari menu tombol.",
            ]);
            $this->sessionService->sendCategories($user->telegram_id);
            return;
        }

        $session->update([
            'state' => 'CHOOSE_PRODUCT',
            'data'  => ['category_id' => $category->id]
        ]);

        $this->sessionService->sendProducts($user->telegram_id, $category);
    }

    private function handleChooseProduct(TelegramUser $user, TelegramSession $session, string $text): void
    {
        if ($text === '🔙 Kembali ke Kategori') {
            $session->update(['state' => 'CHOOSE_CATEGORY', 'data' => []]);
            $this->sessionService->sendCategories($user->telegram_id);
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
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "Pilihan produk tidak valid.",
            ]);
            if ($categoryId) {
                $category = Category::find($categoryId);
                $this->sessionService->sendProducts($user->telegram_id, $category);
            } else {
                $this->sessionService->sendCategories($user->telegram_id);
            }
            return;
        }

        $session->update([
            'state' => 'CHOOSE_VARIANT',
            'data'  => array_merge($session->data, ['product_id' => $product->id])
        ]);

        $this->sessionService->sendProductDetails($user->telegram_id, $product);
    }

    private function handleChooseVariant(TelegramUser $user, TelegramSession $session, string $text): void
    {
        $productId = $session->data['product_id'] ?? null;
        $product = Product::find($productId);

        if ($text === '🔙 Kembali ke Produk') {
            if ($product) {
                $session->update([
                    'state' => 'CHOOSE_PRODUCT',
                    'data'  => ['category_id' => $product->category_id]
                ]);
                $this->sessionService->sendProducts($user->telegram_id, $product->category);
            } else {
                $session->update(['state' => 'CHOOSE_CATEGORY', 'data' => []]);
                $this->sessionService->sendCategories($user->telegram_id);
            }
            return;
        }

        // Parse: "⚡ Varian - Rp50.000"
        $cleanText = preg_replace('/^⚡\s*/', '', $text);
        $parts = explode(' - Rp', $cleanText);
        $variantName = trim($parts[0]);

        $variant = ProductVariant::where('name', $variantName)->where('product_id', $productId)->active()->first();

        if (!$variant) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "Pilihan varian tidak valid.",
            ]);
            if ($product) {
                $this->sessionService->sendProductDetails($user->telegram_id, $product);
            }
            return;
        }

        if ($variant->stock == 0) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "Mohon maaf, varian produk ini sedang habis.",
            ]);
            return;
        }

        $session->update([
            'state' => 'CONFIRM_ORDER',
            'data'  => array_merge($session->data, ['variant_id' => $variant->id])
        ]);

        Telegram::sendMessage([
            'chat_id'      => $user->telegram_id,
            'text'         => "✍️ Silakan masukkan *Catatan Pesanan* atau data yang dibutuhkan (misal: Username Instagram, Link Profile, Nomor HP, atau Email Anda):",
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard'          => [[['text' => '🔙 Batal']]],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    private function handleConfirmOrder(TelegramUser $user, TelegramSession $session, string $text): void
    {
        if ($text === '🔙 Batal') {
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sessionService->sendMenu($user->telegram_id);
            return;
        }

        $variantId = $session->data['variant_id'] ?? null;
        $variant = ProductVariant::find($variantId);

        if (!$variant) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "Terjadi kesalahan. Varian tidak ditemukan.",
            ]);
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sessionService->sendMenu($user->telegram_id);
            return;
        }

        // Generate Invoice Number: INV-YYYYMMDD-000001
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', today())->orderByDesc('id')->first();
        $seq = 1;
        if ($lastOrder && preg_match('/-(\d+)$/', $lastOrder->invoice_number, $matches)) {
            $seq = (int)$matches[1] + 1;
        }
        $invoiceNumber = "INV-{$date}-" . str_pad($seq, 6, '0', STR_PAD_LEFT);

        // Simpan Order
        $order = Order::create([
            'invoice_number'     => $invoiceNumber,
            'telegram_user_id'   => $user->id,
            'product_variant_id' => $variant->id,
            'quantity'           => 1,
            'total_price'        => $variant->price,
            'status'             => OrderStatus::WAITING_PAYMENT,
            'notes'              => $text,
        ]);

        // Simpan Payment Record
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount'   => $order->total_price,
            'status'   => PaymentStatus::UNPAID,
        ]);

        // Potong stok jika stock != -1
        if ($variant->stock > 0) {
            $variant->decrement('stock');
        }

        $session->update([
            'state' => 'WAITING_PAYMENT_PROOF',
            'data'  => ['order_id' => $order->id, 'payment_id' => $payment->id]
        ]);

        $this->sessionService->sendQrisPayment($user->telegram_id, $order);
    }

    private function handleWaitingPaymentProof(TelegramUser $user, TelegramSession $session, $message): void
    {
        $orderId = $session->data['order_id'] ?? null;
        $paymentId = $session->data['payment_id'] ?? null;
        $order = Order::find($orderId);
        $payment = Payment::find($paymentId);

        $text = $message->getText();

        if ($text === '❌ Batalkan Pesanan') {
            if ($order && $order->status === OrderStatus::WAITING_PAYMENT) {
                $order->update(['status' => OrderStatus::CANCELLED]);
                // Balikkan stok jika tidak unlimited
                $variant = $order->productVariant;
                if ($variant && $variant->stock != -1) {
                    $variant->increment('stock');
                }
                Telegram::sendMessage([
                    'chat_id' => $user->telegram_id,
                    'text'    => "❌ Pesanan `{$order->invoice_number}` berhasil dibatalkan.",
                ]);
            }
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sessionService->sendMenu($user->telegram_id);
            return;
        }

        // Cek apakah pesan berisi foto
        $photos = $message->getPhoto();
        if (empty($photos) || ($photos instanceof \Illuminate\Support\Collection && $photos->isEmpty())) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "⚠️ Harap kirimkan gambar/foto bukti pembayaran Anda. Jika ingin membatalkan, pilih tombol '❌ Batalkan Pesanan'.",
            ]);
            return;
        }

        if (!$order || !$payment) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "Terjadi kesalahan sistem. Pesanan tidak ditemukan.",
            ]);
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sessionService->sendMenu($user->telegram_id);
            return;
        }

        // Ambil foto kualitas terbaik (terakhir di array atau Collection)
        $photo = null;
        if ($photos instanceof \Illuminate\Support\Collection) {
            $photo = $photos->last();
        } elseif (is_array($photos)) {
            $photo = end($photos);
        }

        if (!$photo) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "⚠️ Gagal membaca berkas foto. Harap kirim kembali bukti pembayaran Anda.",
            ]);
            return;
        }

        $fileId = is_array($photo) ? ($photo['file_id'] ?? null) : ($photo->file_id ?? $photo->get('file_id'));

        try {
            $file = Telegram::getFile(['file_id' => $fileId]);
            $filePathTelegram = $file->getFilePath();

            // Unduh file ke local storage
            $botToken = config('telegram.bots.mybot.token') ?? env('TELEGRAM_BOT_TOKEN');
            $url = "https://api.telegram.org/file/bot" . $botToken . "/{$filePathTelegram}";
            $fileContent = file_get_contents($url);

            $localFileName = "proof_{$order->invoice_number}_" . time() . ".jpg";
            $localPath = "payment_proofs/{$localFileName}";
            Storage::disk('public')->put($localPath, $fileContent);

            // Simpan bukti
            PaymentProof::create([
                'payment_id'       => $payment->id,
                'file_path'        => $localPath,
                'file_name'        => $localFileName,
                'telegram_file_id' => $fileId,
                'uploaded_at'      => now(),
            ]);

            // Update Statuses
            $payment->update(['status' => PaymentStatus::WAITING]);
            $order->update(['status' => OrderStatus::WAITING_VERIFICATION]);

            Telegram::sendMessage([
                'chat_id'    => $user->telegram_id,
                'text'       => "✅ *BUKTI PEMBAYARAN DITERIMA*\n\nTerima kasih, bukti pembayaran untuk invoice `{$order->invoice_number}` telah kami terima dan sedang diverifikasi oleh admin.\n\nAnda akan menerima notifikasi otomatis di sini setelah pembayaran disetujui.",
                'parse_mode' => 'Markdown',
            ]);

            // Kirim notifikasi ke Telegram ID Admin jika ada
            $adminTelegramId = Setting::get('admin_telegram_id');
            if ($adminTelegramId) {
                try {
                    $order->load(['productVariant.product']);
                    $productName = $order->productVariant->product->name ?? 'Produk';
                    $variantName = $order->productVariant->name ?? 'Varian';
                    $notes = $order->notes ?: '-';
                    $userDisplay = $user->full_name . ($user->username ? " (@{$user->username})" : '');

                    $caption = "🔔 *NOTIFIKASI VERIFIKASI PEMBAYARAN*\n\n" .
                               "Invoice: `{$order->invoice_number}`\n" .
                               "Produk: *{$productName} - {$variantName}*\n" .
                               "Nominal: *Rp" . number_format($order->total_price, 0, ',', '.') . "*\n" .
                               "Pembeli: *{$userDisplay}*\n" .
                               "Catatan: `{$notes}`\n\n" .
                               "Silakan lakukan verifikasi bukti pembayaran di bawah:";

                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                ['text' => '✅ Verifikasi', 'callback_data' => "verify_order_{$order->id}"],
                                ['text' => '❌ Tolak', 'callback_data' => "reject_order_{$order->id}"],
                            ]
                        ]
                    ];

                    if (!empty($fileId)) {
                        Telegram::sendPhoto([
                            'chat_id'      => $adminTelegramId,
                            'photo'        => $fileId,
                            'caption'      => $caption,
                            'parse_mode'   => 'Markdown',
                            'reply_markup' => json_encode($keyboard),
                        ]);
                    } else {
                        Telegram::sendMessage([
                            'chat_id'      => $adminTelegramId,
                            'text'         => $caption,
                            'parse_mode'   => 'Markdown',
                            'reply_markup' => json_encode($keyboard),
                        ]);
                    }
                } catch (\Exception $ex) {
                    Log::error("Gagal mengirim notifikasi bukti bayar ke admin: " . $ex->getMessage());
                }
            }

            // Selesai, balikkan ke menu utama
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sessionService->sendMenu($user->telegram_id);

        } catch (\Exception $e) {
            Log::error("Gagal mengunduh bukti transfer Telegram: " . $e->getMessage());
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "❌ Gagal memproses bukti pembayaran. Harap coba lagi atau hubungi admin.",
            ]);
        }
    }

    private function handleMyOrders(TelegramUser $user): void
    {
        $orders = Order::where('telegram_user_id', $user->id)->latest()->take(5)->get();

        if ($orders->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_id,
                'text'    => "Anda belum memiliki riwayat pesanan.",
            ]);
            return;
        }

        $text = "📦 *5 PESANAN TERAKHIR ANDA:*\n\n";
        foreach ($orders as $order) {
            $statusLabel = $order->status->label();
            $text .= "Invoice: `{$order->invoice_number}`\n" .
                     "Produk: *{$order->productVariant->product->name} - {$order->productVariant->name}*\n" .
                     "Harga: *Rp" . number_format($order->total_price, 0, ',', '.') . "*\n" .
                     "Status: *{$statusLabel}*\n" .
                     "Tanggal: " . $order->created_at->format('d/m/Y H:i') . "\n";

            if ($order->status === OrderStatus::WAITING_PAYMENT) {
                $text .= "👉 _Silakan lakukan pembayaran dan upload bukti bayar._\n";
            }
            $text .= "---------------------------------\n";
        }

        Telegram::sendMessage([
            'chat_id'    => $user->telegram_id,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    /**
     * Menangani callback query dari tombol Inline Keyboard Telegram Admin.
     */
    private function handleCallbackQuery(CallbackQuery $callbackQuery): void
    {
        $callbackQueryId = $callbackQuery->getId();
        $data = $callbackQuery->getData();
        $from = $callbackQuery->getFrom();
        $fromTelegramId = $from->getId();
        $adminUsername = $from->getUsername() ? "@" . $from->getUsername() : ($from->getFirstName() ?? 'Admin');
        $message = $callbackQuery->getMessage();

        if (str_starts_with($data, 'verify_order_') || str_starts_with($data, 'reject_order_')) {
            // 1. Keamanan: Cek Otorisasi Admin
            $adminTelegramId = Setting::get('admin_telegram_id');
            $isAdmin = ($adminTelegramId && (string)$adminTelegramId === (string)$fromTelegramId);

            if (!$isAdmin) {
                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQueryId,
                    'text'              => '⚠️ Anda tidak memiliki akses untuk melakukan tindakan ini.',
                    'show_alert'        => true,
                ]);
                return;
            }

            $isVerify = str_starts_with($data, 'verify_order_');
            $orderId = (int) str_replace($isVerify ? 'verify_order_' : 'reject_order_', '', $data);

            $order = Order::with(['payment', 'telegramUser', 'productVariant.product'])->find($orderId);

            if (!$order || !$order->payment) {
                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQueryId,
                    'text'              => '❌ Pesanan atau data pembayaran tidak ditemukan.',
                    'show_alert'        => true,
                ]);
                return;
            }

            // 2. Audit & Idempotensi: Pengecekan jika status sudah diproses
            if ($order->status !== OrderStatus::WAITING_VERIFICATION || $order->payment->status !== PaymentStatus::WAITING) {
                $statusLabel = $order->status->label();
                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQueryId,
                    'text'              => "⚠️ Pesanan `{$order->invoice_number}` sudah diproses sebelumnya (Status: {$statusLabel}).",
                    'show_alert'        => true,
                ]);

                // Hapus tombol keyboard agar tidak bisa diklik lagi
                if ($message) {
                    try {
                        Telegram::editMessageReplyMarkup([
                            'chat_id'      => $message->getChat()->getId(),
                            'message_id'   => $message->getMessageId(),
                            'reply_markup' => json_encode(['inline_keyboard' => []]),
                        ]);
                    } catch (\Exception $e) {
                        // Abaikan jika tidak bisa di-edit
                    }
                }
                return;
            }

            $admin = Admin::first();
            $payment = $order->payment;
            $productName = $order->productVariant->product->name ?? 'Produk';
            $variantName = $order->productVariant->name ?? 'Varian';
            $buyerTelegramId = $order->telegramUser->telegram_id;

            if ($isVerify) {
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
                try {
                    Telegram::sendMessage([
                        'chat_id'    => $buyerTelegramId,
                        'text'       => "✅ *PEMBAYARAN DIVERIFIKASI*\n\nPembayaran untuk invoice `{$order->invoice_number}` telah diverifikasi oleh admin. Terima kasih!\n\nProduk Anda telah/sedang dikirimkan di atas.",
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim notifikasi verifikasi ke pembeli: " . $e->getMessage());
                }

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
                    try {
                        $hasPhoto = !empty($message->getPhoto());
                        if ($hasPhoto) {
                            Telegram::editMessageCaption([
                                'chat_id'      => $message->getChat()->getId(),
                                'message_id'   => $message->getMessageId(),
                                'caption'      => $updatedCaption,
                                'parse_mode'   => 'Markdown',
                                'reply_markup' => json_encode(['inline_keyboard' => []]),
                            ]);
                        } else {
                            Telegram::editMessageText([
                                'chat_id'      => $message->getChat()->getId(),
                                'message_id'   => $message->getMessageId(),
                                'text'         => $updatedCaption,
                                'parse_mode'   => 'Markdown',
                                'reply_markup' => json_encode(['inline_keyboard' => []]),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error("Gagal edit message admin: " . $e->getMessage());
                    }
                }

                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQueryId,
                    'text'              => '✅ Pesanan berhasil diverifikasi!',
                    'show_alert'        => false,
                ]);
            } else {
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
                try {
                    Telegram::sendMessage([
                        'chat_id'    => $buyerTelegramId,
                        'text'       => "❌ *PEMBAYARAN DITOLAK*\n\nPembayaran untuk invoice `{$order->invoice_number}` ditolak oleh admin.\n\nSilakan periksa kembali bukti transfer Anda dan kirimkan ulang bukti pembayaran yang valid.",
                        'parse_mode' => 'Markdown',
                    ]);
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim notifikasi penolakan ke pembeli: " . $e->getMessage());
                }

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
                    try {
                        $hasPhoto = !empty($message->getPhoto());
                        if ($hasPhoto) {
                            Telegram::editMessageCaption([
                                'chat_id'      => $message->getChat()->getId(),
                                'message_id'   => $message->getMessageId(),
                                'caption'      => $updatedCaption,
                                'parse_mode'   => 'Markdown',
                                'reply_markup' => json_encode(['inline_keyboard' => []]),
                            ]);
                        } else {
                            Telegram::editMessageText([
                                'chat_id'      => $message->getChat()->getId(),
                                'message_id'   => $message->getMessageId(),
                                'text'         => $updatedCaption,
                                'parse_mode'   => 'Markdown',
                                'reply_markup' => json_encode(['inline_keyboard' => []]),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error("Gagal edit message admin: " . $e->getMessage());
                    }
                }

                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQueryId,
                    'text'              => '❌ Pesanan telah ditolak.',
                    'show_alert'        => false,
                ]);
            }
        }
    }
}
