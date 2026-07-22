<?php

namespace App\Services\Telegram;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\TelegramSession;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\FileUpload\InputFile;

class OrderHandler
{
    public function __construct(
        private readonly MessageSender $sender
    ) {}

    /**
     * Konfirmasi order dan buat invoice.
     */
    public function handleConfirmOrder(TelegramUser $user, TelegramSession $session, string $text): void
    {
        if ($text === '🔙 Batal' || $text === KeyboardBuilder::BTN_CANCEL_ORDER) {
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sender->text(
                $user->telegram_id,
                "Pesanan dibatalkan. Kembali ke menu utama:",
                KeyboardBuilder::mainMenu()
            );
            return;
        }

        $variantId = $session->data['variant_id'] ?? null;
        $variant = ProductVariant::find($variantId);

        if (!$variant) {
            $this->sender->text(
                $user->telegram_id,
                "Terjadi kesalahan. Varian produk tidak ditemukan."
            );
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sender->text(
                $user->telegram_id,
                "Silakan pilih menu utama:",
                KeyboardBuilder::mainMenu()
            );
            return;
        }

        // Gunakan method generateInvoiceNumber() dari model Order (Bug #4 fix)
        $invoiceNumber = Order::generateInvoiceNumber();

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

        // Potong stok jika stock > 0
        if ($variant->stock > 0) {
            $variant->decrement('stock');
        }

        $session->update([
            'state' => 'WAITING_PAYMENT_PROOF',
            'data'  => ['order_id' => $order->id, 'payment_id' => $payment->id]
        ]);

        $this->sendQrisPayment($user, $order);
    }

    /**
     * Kirim invoice & instruksi/foto QRIS.
     */
    public function sendQrisPayment(TelegramUser $user, Order $order): void
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
            $this->sender->photo(
                $user->telegram_id,
                InputFile::create(storage_path('app/public/' . $qrisImage), 'qris.png'),
                "Scan QRIS a/n: {$accountName}"
            );
        } else {
            $this->sender->text(
                $user->telegram_id,
                "⚠️ *Metode QRIS belum aktif.* Harap hubungi admin.",
                null,
                'Markdown'
            );
        }

        $this->sender->text(
            $user->telegram_id,
            $invoiceDetails,
            KeyboardBuilder::cancelOrder(KeyboardBuilder::BTN_CANCEL_ORDER),
            'Markdown'
        );
    }

    /**
     * Tangani pengiriman foto bukti pembayaran dari user.
     */
    public function handleWaitingPaymentProof(TelegramUser $user, TelegramSession $session, $message): void
    {
        $orderId = $session->data['order_id'] ?? null;
        $paymentId = $session->data['payment_id'] ?? null;
        $order = Order::find($orderId);
        $payment = Payment::find($paymentId);

        $text = $message->getText();

        if ($text === KeyboardBuilder::BTN_CANCEL_ORDER || $text === '❌ Batalkan Pesanan') {
            if ($order && $order->status === OrderStatus::WAITING_PAYMENT) {
                $order->update(['status' => OrderStatus::CANCELLED]);
                // Balikkan stok jika tidak unlimited (-1)
                $variant = $order->productVariant;
                if ($variant && $variant->stock != -1) {
                    $variant->increment('stock');
                }
                $this->sender->text(
                    $user->telegram_id,
                    "❌ Pesanan `{$order->invoice_number}` berhasil dibatalkan.",
                    null,
                    'Markdown'
                );
            }
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sender->text(
                $user->telegram_id,
                "Kembali ke menu utama:",
                KeyboardBuilder::mainMenu()
            );
            return;
        }

        // Cek apakah pesan berisi foto
        $photos = $message->getPhoto();
        if (empty($photos) || ($photos instanceof \Illuminate\Support\Collection && $photos->isEmpty())) {
            $this->sender->text(
                $user->telegram_id,
                "⚠️ Harap kirimkan gambar/foto bukti pembayaran Anda. Jika ingin membatalkan, pilih tombol '❌ Batalkan Pesanan'."
            );
            return;
        }

        if (!$order || !$payment) {
            $this->sender->text(
                $user->telegram_id,
                "Terjadi kesalahan sistem. Pesanan tidak ditemukan."
            );
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sender->text(
                $user->telegram_id,
                "Silakan pilih menu utama:",
                KeyboardBuilder::mainMenu()
            );
            return;
        }

        // Ambil foto kualitas terbaik
        $photo = null;
        if ($photos instanceof \Illuminate\Support\Collection) {
            $photo = $photos->last();
        } elseif (is_array($photos)) {
            $photo = end($photos);
        }

        if (!$photo) {
            $this->sender->text(
                $user->telegram_id,
                "⚠️ Gagal membaca berkas foto. Harap kirim kembali bukti pembayaran Anda."
            );
            return;
        }

        $fileId = is_array($photo) ? ($photo['file_id'] ?? null) : ($photo->file_id ?? $photo->get('file_id'));

        try {
            $file = \Telegram\Bot\Laravel\Facades\Telegram::getFile(['file_id' => $fileId]);
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

            // Update status
            $payment->update(['status' => PaymentStatus::WAITING]);
            $order->update(['status' => OrderStatus::WAITING_VERIFICATION]);

            $this->sender->text(
                $user->telegram_id,
                "✅ *BUKTI PEMBAYARAN DITERIMA*\n\nTerima kasih, bukti pembayaran untuk invoice `{$order->invoice_number}` telah kami terima dan sedang diverifikasi oleh admin.\n\nAnda akan menerima notifikasi otomatis di sini setelah pembayaran disetujui.",
                null,
                'Markdown'
            );

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

                    $keyboard = KeyboardBuilder::adminVerification($order->id);

                    if (!empty($fileId)) {
                        $this->sender->photo(
                            $adminTelegramId,
                            $fileId,
                            $caption,
                            $keyboard,
                            'Markdown'
                        );
                    } else {
                        $this->sender->text(
                            $adminTelegramId,
                            $caption,
                            $keyboard,
                            'Markdown'
                        );
                    }
                } catch (\Throwable $ex) {
                    Log::error("Gagal mengirim notifikasi bukti bayar ke admin: " . $ex->getMessage());
                }
            }

            // Selesai, kembalikan ke menu utama
            $session->update(['state' => 'MENU', 'data' => []]);
            $this->sender->text(
                $user->telegram_id,
                "Silakan pilih menu:",
                KeyboardBuilder::mainMenu()
            );

        } catch (\Throwable $e) {
            Log::error("Gagal mengunduh bukti transfer Telegram: " . $e->getMessage());
            $this->sender->text(
                $user->telegram_id,
                "❌ Gagal memproses bukti pembayaran. Harap coba lagi atau hubungi admin."
            );
        }
    }

    /**
     * Tampilkan 5 pesanan terakhir user.
     */
    public function handleMyOrders(TelegramUser $user): void
    {
        $orders = Order::where('telegram_user_id', $user->id)->latest()->take(5)->get();

        if ($orders->isEmpty()) {
            $this->sender->text(
                $user->telegram_id,
                "Anda belum memiliki riwayat pesanan."
            );
            return;
        }

        $text = "📦 *5 PESANAN TERAKHIR ANDA:*\n\n";
        foreach ($orders as $order) {
            $statusLabel = $order->status->label();
            $productName = $order->productVariant->product->name ?? 'Produk';
            $variantName = $order->productVariant->name ?? 'Varian';

            $text .= "Invoice: `{$order->invoice_number}`\n" .
                     "Produk: *{$productName} - {$variantName}*\n" .
                     "Harga: *Rp" . number_format($order->total_price, 0, ',', '.') . "*\n" .
                     "Status: *{$statusLabel}*\n" .
                     "Tanggal: " . $order->created_at->format('d/m/Y H:i') . "\n";

            if ($order->status === OrderStatus::WAITING_PAYMENT) {
                $text .= "👉 _Silakan lakukan pembayaran dan upload bukti bayar._\n";
            }
            $text .= "---------------------------------\n";
        }

        $this->sender->text(
            $user->telegram_id,
            $text,
            null,
            'Markdown'
        );
    }
}
