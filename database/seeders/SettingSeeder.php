<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'store_name',           'value' => 'Nexora Digital',                        'group' => 'general'],
            ['key' => 'store_description',    'value' => 'Toko produk digital terpercaya',        'group' => 'general'],
            ['key' => 'admin_telegram_id',    'value' => '',                                       'group' => 'general'],

            // Bot Messages
            ['key' => 'bot_welcome_message',  'value' => "Halo {name}! 👋\n\nSelamat datang di *Nexora Digital* 🛍️\n\nToko produk digital terpercaya dengan harga terjangkau.\n\nSilakan pilih menu di bawah:", 'group' => 'bot'],
            ['key' => 'bot_help_message',     'value' => "❓ *BANTUAN*\n\nCara Berbelanja:\n1. Pilih menu 🛍 Produk\n2. Pilih kategori\n3. Pilih produk & varian\n4. Checkout\n5. Bayar via QRIS\n6. Upload bukti bayar\n7. Tunggu verifikasi\n8. Produk dikirim otomatis\n\nKesulitan? Hubungi admin: /admin", 'group' => 'bot'],
            ['key' => 'bot_payment_info',     'value' => "💳 *CARA PEMBAYARAN*\n\n✅ Pembayaran menggunakan QRIS\n✅ Scan QR Code yang dikirimkan\n✅ Upload bukti transfer setelah bayar\n✅ Verifikasi dalam 1x24 jam\n\nPastikan nominal transfer sesuai dengan invoice!", 'group' => 'bot'],
            ['key' => 'bot_contact_admin',    'value' => "☎️ *HUBUNGI ADMIN*\n\nJika ada pertanyaan atau kendala, silakan:\n\n📱 Kirim pesan ke admin kami\n⏰ Jam layanan: 08.00 - 22.00 WIB\n\nAdmin akan membalas secepat mungkin.",           'group' => 'bot'],

            // QRIS
            ['key' => 'qris_image_path',      'value' => '',   'group' => 'payment'],
            ['key' => 'qris_account_name',    'value' => 'Nexora Digital', 'group' => 'payment'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
