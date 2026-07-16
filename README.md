# ⚡ Nexora Digital

Nexora Digital adalah platform penjualan produk digital otomatis menggunakan **Telegram Bot** yang terintegrasi secara *real-time* dengan **Dashboard Admin** berbasis **Laravel 12**. 

Sistem ini dirancang dengan alur transaksi yang *seamless* untuk pelanggan di Telegram, sementara administrator mengelola inventaris, memproses verifikasi pembayaran (QRIS), dan memantau analitik penjualan melalui antarmuka web yang modern dan responsif.

---

## 🚀 Fitur Utama

### 1. Dashboard & Sistem Manajemen Admin
*   **Analitik Real-Time:** Statistik pendapatan harian & bulanan, jumlah pengguna Telegram terdaftar, total produk, serta daftar produk terlaris.
*   **Katalog Fleksibel:** Pengelolaan Kategori, Produk, dan Varian (stok tak terbatas menggunakan nilai `-1`).
*   **Sistem Penyimpanan File Aman:** File produk digital disimpan di penyimpanan privat (`storage/app/private_files`) yang tidak bisa diakses langsung via web publik.
*   **Verifikasi Pembayaran QRIS:** Form verifikasi manual dilengkapi dengan visualisasi bukti transfer gambar dan input alasan penolakan jika pembayaran tidak valid.
*   **Manajemen User Telegram:** Daftar pembeli aktif dengan opsi blokir/buka blokir pengguna yang melanggar ketentuan.
*   **Sistem Broadcast Pesan:** Mengirim pesan promosi/informasi ke seluruh pengguna bot secara efisien menggunakan **Laravel Queue (Antrean)** untuk menghindari batas laju limit API Telegram.
*   **Activity Audit Log:** Mencatat seluruh tindakan penting administrator untuk menjaga keamanan data.

### 2. Logika Telegram Bot (User Flow)
*   **State-Machine Interactive:** Menavigasi menu katalog, sub-kategori, dan varian secara interaktif melalui inline keyboard.
*   **Invoice Generator:** Membuat invoice otomatis dengan total harga yang harus dibayar.
*   **Upload Bukti Bayar:** Mendeteksi pengiriman gambar bukti transfer QRIS oleh pembeli secara langsung di ruang obrolan Telegram.
*   **Pengiriman Otomatis (Instant Delivery):** Setelah admin menekan tombol "Setujui", bot Telegram secara otomatis mengirimkan produk sesuai tipenya:
    *   `TEXT`: Mengirimkan detail lisensi, kode voucher, atau teks akun.
    *   `FILE`: Mengirimkan dokumen/arsip privat langsung di chat Telegram.
    *   `MANUAL`: Mengirimkan instruksi khusus bagi produk yang membutuhkan penanganan manual oleh admin.

---

## 🛠️ Spesifikasi Teknologi

*   **Core Framework:** Laravel 12 & PHP 8.3
*   **Database:** MySQL 8.0 & Redis (untuk antrean dan caching)
*   **Telegram Library:** `irazasyed/telegram-bot-sdk:^3.16`
*   **Styling Admin:** Bootstrap 5 & Mazer Dashboard Theme
*   **Containerization:** Laravel Sail (Docker)

---

## 📦 Panduan Instalasi & Konfigurasi

### 1. Prasyarat
Pastikan Anda sudah menginstal:
*   [Docker](https://www.docker.com/) & Docker Compose
*   [Git](https://git-scm.com/)

### 2. Setup Project & Docker Sail
Kloning repositori dan masuk ke direktori proyek:
```bash
cd nexora-digital
```

Salin file konfigurasi lingkungan:
```bash
cp .env.example .env
```

Jalankan container Docker Sail (MySQL, Redis, Mailpit, PHP 8.3):
```bash
./vendor/bin/sail up -d
```

### 3. Dependency & Database Initialization
Jalankan composer install dan migrasi database beserta data awal (seeding) di dalam container:
```bash
# Install NPM dependencies dan build assets
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

# Generate APP Key & Inisialisasi Database
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

### 4. Konfigurasi Kunci Lingkungan (`.env`)
Buka file `.env` Anda dan isi token Telegram Bot Anda:
```env
# Konfigurasi Telegram Bot
TELEGRAM_BOT_TOKEN="1234567890:ABCdefGhIJKlmNoPQRsTUVwxyZ"
TELEGRAM_BOT_NAME="NexoraDigital_bot"
```

---

## 🤖 Menghubungkan Telegram Bot Webhook

Agar Telegram Bot dapat menerima pesan dan berinteraksi secara *real-time*, Anda harus mendaftarkan URL Webhook ke server Telegram.

1.  **Expose Server Lokal (Development):**
    Jika Anda mengembangkan di komputer lokal, gunakan *ngrok* atau *expose* untuk membuat tunnel HTTPS:
    ```bash
    ngrok http 80
    ```
    *Salin URL HTTPS yang dihasilkan oleh ngrok (contoh: `https://xxxx-xxxx.ngrok-free.dev`).*

2.  **Daftarkan Webhook:**
    Buka peramban (browser) dan akses URL berikut untuk mendaftarkan webhook:
    ```
    https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook?url=https://<your-ngrok-subdomain>.ngrok-free.dev/telegram/webhook
    ```
    *Pastikan respons dari Telegram mengembalikan `{"ok":true,"result":true,"description":"Webhook was set"}`.*

3.  **Jalankan Queue Worker (Penting):**
    Jalankan antrean worker agar background job pengiriman file dan broadcast berjalan:
    ```bash
    ./vendor/bin/sail artisan queue:work
    ```

---

## 🔑 Akun Akses Default (Seeder)

Setelah berhasil melakukan seeding, Anda dapat masuk ke Dashboard Admin menggunakan akun default berikut:

*   **URL Dashboard:** `http://localhost/admin`
*   **Email:** `admin@nexoradigital.com`
*   **Password:** `admin123`

---

## 📁 Struktur Penyimpanan File
*   **Bukti Pembayaran:** Disimpan di `public/storage/payment-proofs/` (dapat diakses publik untuk verifikasi).
*   **Produk Digital:** Disimpan di `storage/app/private_files/digital-files/` (terlindungi sepenuhnya dari akses publik langsung).
