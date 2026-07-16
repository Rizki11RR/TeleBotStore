<?php

namespace App\Services;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\DigitalFile;
use App\Models\Setting;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    /**
     * Kirim produk digital ke user Telegram.
     */
    public function deliver(Order $order): bool
    {
        try {
            $variant = $order->productVariant;
            if (!$variant) {
                return false;
            }

            $product = $variant->product;
            $chatId = $order->telegramUser->telegram_id;
            $productName = $product->name;
            $variantName = $variant->name;

            // Judul pengiriman
            $headerText = "📦 *PENGIRIMAN PRODUK*\n\n" .
                          "Invoice: `{$order->invoice_number}`\n" .
                          "Produk: *{$productName} - {$variantName}*\n\n";

            // Jika produk bertipe akun
            if ($product->type === 'account') {
                $account = $variant->accounts()->where('is_sold', false)->first();

                if (!$account) {
                    // Notifikasi stok kosong ke pembeli
                    Telegram::sendMessage([
                        'chat_id'    => $chatId,
                        'text'       => "⚠️ *STOK HABIS*\n\nMaaf, stok kredensial akun untuk produk *{$productName} - {$variantName}* saat ini sedang habis.\n\nAdmin telah dinotifikasi untuk melakukan restock. Pesanan Anda akan segera dikirimkan setelah stok tersedia oleh admin.",
                        'parse_mode' => 'Markdown',
                    ]);

                    // Notifikasi ke admin
                    $adminChatId = Setting::get('admin_telegram_id');
                    if (!empty($adminChatId)) {
                        try {
                            Telegram::sendMessage([
                                'chat_id'    => $adminChatId,
                                'text'       => "🚨 *STOK HABIS (NOTIFIKASI ADMIN)* 🚨\n\nStok kredensial akun untuk produk *{$productName} - {$variantName}* telah habis.\n\nPembeli dengan Invoice `{$order->invoice_number}` belum menerima akun. Segera lakukan restock pada dashboard admin.",
                                'parse_mode' => 'Markdown',
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Gagal mengirim notifikasi stok habis ke admin: " . $e->getMessage());
                        }
                    }

                    $order->update(['status' => OrderStatus::PAID]);
                    return false;
                }

                // Tandai sebagai terjual
                $account->update([
                    'is_sold'  => true,
                    'sold_at'  => now(),
                    'order_id' => $order->id,
                ]);

                // Sinkronisasi stok varian
                $variant->update([
                    'stock' => $variant->accounts()->where('is_sold', false)->count(),
                ]);

                // Kirim kredensial ke pembeli
                Telegram::sendMessage([
                    'chat_id'    => $chatId,
                    'text'       => $headerText . "🔑 *Detail Akun / Kredensial:*\n\n" .
                                    "*Username/Email:* `{$account->username_email}`\n" .
                                    "*Password:* `{$account->password}`\n\n" .
                                    "Simpan data di atas dengan aman. Terima kasih telah berbelanja!",
                    'parse_mode' => 'Markdown',
                ]);

                $order->update(['status' => OrderStatus::COMPLETED]);
                return true;
            }

            $digitalFile = $variant->digitalFile;
            if (!$digitalFile) {
                // Jika tidak ada file digital, dianggap dikirim manual
                $order->update(['status' => OrderStatus::COMPLETED]);
                return true;
            }

            $chatId = $order->telegramUser->telegram_id;
            $productName = $variant->product->name;
            $variantName = $variant->name;

            // Judul pengiriman
            $headerText = "📦 *PENGIRIMAN PRODUK*\n\n" .
                          "Invoice: `{$order->invoice_number}`\n" .
                          "Produk: *{$productName} - {$variantName}*\n\n";

            switch ($digitalFile->delivery_type) {
                case DeliveryType::TEXT:
                    Telegram::sendMessage([
                        'chat_id'    => $chatId,
                        'text'       => $headerText . "🔑 *Detail Akses / Lisensi:*\n\n" . $digitalFile->content,
                        'parse_mode' => 'Markdown',
                    ]);
                    $order->update(['status' => OrderStatus::COMPLETED]);
                    break;

                case DeliveryType::FILE:
                    // Kirim pesan header dulu
                    Telegram::sendMessage([
                        'chat_id'    => $chatId,
                        'text'       => $headerText . "File Anda sedang dikirim di bawah ini:",
                        'parse_mode' => 'Markdown',
                    ]);

                    $filePath = Storage::disk('private')->path($digitalFile->file_path);

                    if (!file_exists($filePath)) {
                        Log::error("File digital tidak ditemukan: " . $filePath);
                        Telegram::sendMessage([
                            'chat_id' => $chatId,
                            'text'    => "❌ File gagal dikirim karena file tidak ditemukan di server. Silakan hubungi admin.",
                        ]);
                        return false;
                    }

                    Telegram::sendDocument([
                        'chat_id'  => $chatId,
                        'document' => \Telegram\Bot\FileUpload\InputFile::create($filePath, $digitalFile->file_name),
                    ]);

                    $order->update(['status' => OrderStatus::COMPLETED]);
                    break;

                case DeliveryType::MANUAL:
                default:
                    $manualText = $digitalFile->notes ?: "Pesanan Anda sedang diproses secara manual oleh admin. Silakan tunggu informasi selanjutnya.";
                    Telegram::sendMessage([
                        'chat_id'    => $chatId,
                        'text'       => $headerText . "ℹ️ *Informasi Pengiriman:*\n\n" . $manualText,
                        'parse_mode' => 'Markdown',
                    ]);
                    // Untuk manual, status ke PAID dulu, admin yang akan set ke COMPLETED setelah barang dikirim
                    $order->update(['status' => OrderStatus::PAID]);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Gagal mengirim produk digital: " . $e->getMessage());
            return false;
        }
    }
}
