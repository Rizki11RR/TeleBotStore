# Audit & Refactor — TeleBotStore (Nexora Digital)

## 1. Hasil audit per poin

| # | Poin dicek | Status sebelum refactor | Temuan |
|---|---|---|---|
| 1 | Route webhook | ✅ OK | `POST /telegram/webhook`, sudah dikecualikan dari CSRF di `bootstrap/app.php` (`validateCsrfTokens(except: ['telegram/webhook'])`) |
| 2 | WebhookController | ⚠️ OK tapi terlalu "pintar" untuk sebuah controller | Sudah benar memanggil `Telegram::getWebhookUpdate()`, tapi semua nama class di-hardcode ke root namespace `App\Services` — kurang rapi |
| 3 | TelegramBotService | 🐞 **Bug ditemukan** | Satu file 765 baris melakukan semua hal: routing command, routing callback, order, payment, delivery notif. Melanggar Single Responsibility. Lihat bug #1 & #2 di bawah |
| 4 | Command Handler | ✅ Tidak pakai `commandsHandler()` SDK bawaan | Desain manual (`if ($text === ...)`) — valid, tapi rawan typo string karena diulang di banyak tempat. Sudah dirapikan jadi konstanta di `KeyboardBuilder` |
| 5 | CallbackQuery Handler | 🐞 **Bug ditemukan** | Sudah ditangani (`getCallbackQuery()` dicek duluan), TAPI kalau ada exception di tengah proses verifikasi (mis. `DeliveryService::deliver()` melempar error), `answerCallbackQuery()` di baris bawahnya **tidak pernah tereksekusi** → tombol admin akan spinner terus tanpa respon. Ini bug nyata dengan gejala persis "tombol ditekan, tidak ada respon", meskipun terjadi di sisi admin, bukan pembeli |
| 6 | Inline Keyboard | ✅ OK | Struktur JSON `inline_keyboard` valid |
| 7 | callback_data | ✅ OK | Format `verify_order_{id}` / `reject_order_{id}` konsisten dengan parsing `str_starts_with()` |
| 8 | Router callback | ⚠️ Tidak ada router terpisah | Logic dispatch ada di dalam `handleCallbackQuery()`, sekarang diekstrak ke `CallbackRouter` (Open/Closed — mudah tambah callback baru) |
| 9 | Semua Command | 🐞 **Bug ditemukan** | Lihat bug #3 (`array_merge(null, ...)`) |
| 10 | Semua Service | 🐞 Lihat bug #4 (duplikasi logic invoice number) |
| 11 | Queue | ✅ Tidak dipakai (komentar di controller menyebut "queue" tapi kode sebenarnya sinkron) — untuk skala kecil di shared hosting ini justru **lebih aman**, karena shared hosting biasanya tidak menjalankan `queue:work` sebagai daemon. Tidak saya ubah jadi async |
| 12 | Config Telegram | ✅ OK | `config/telegram.php` (dari package) dipakai lewat `config('telegram.bots.mybot.token')` |
| 13 | Telegram SDK | ✅ OK | `irazasyed/telegram-bot-sdk ^3.16`, kompatibel dengan Laravel 12 / PHP 8.4 |
| 14 | Middleware | ✅ OK | CSRF exception sudah benar (lihat #1) |
| 15 | Exception Handling | 🐞 **Bug ditemukan** | Semua exception ditelan (`Log::error` lalu diam), user/admin tidak pernah tahu ada error. Ini penyebab utama gejala "tidak ada respon tanpa error apapun" |
| 16 | Log Laravel | ⚠️ Pasif | Log sudah ada tapi tidak actionable karena tidak ada notifikasi balik |
| 17 | Dependency | ✅ OK | `composer.json` konsisten |
| 18 | Composer Package | ✅ OK | Tidak ada versi conflicting |
| 19 | Database | ✅ Schema konsisten dengan model & scope (`is_active`, `sort_order`, dll. sudah match migration) |
| 20 | ENV | ✅ Tidak ada dependency env yang hilang untuk fitur callback |

---

## 2. Bug yang ditemukan & diperbaiki

### Bug #1 — Exception ditelan total tanpa jejak ke user (root cause paling mungkin dari gejala "bot diam")
**File:** `WebhookController.php`, `TelegramBotService.php` (lama)
**Penyebab:** Semua `catch (\Throwable $e)` hanya memanggil `Log::error()`. Response ke Telegram tetap `200 OK` apa pun yang terjadi di dalam. Kalau ada bug di logic yang lebih kompleks (kategori/produk/order), user tidak mendapat pesan apapun — persis "ditekan, tidak ada respon, tidak ada error di Telegram".
**Perbaikan:** Struktur baru tetap mempertahankan pola "selalu 200 OK ke Telegram" (ini justru benar secara desain webhook), tapi menambahkan **jaring pengaman di level callback**: `CallbackRouter::handle()` menjamin `answerCallback()` selalu terpanggil lewat try/catch yang eksplisit, bukan berharap kode di bawahnya "kebetulan" sampai ke situ.

### Bug #2 — `answerCallbackQuery()` bisa tidak pernah terpanggil kalau ada exception di tengah proses verifikasi
**File:** `TelegramBotService.php` (lama), method `handleCallbackQuery()`
**Penyebab:** Urutan kode: update DB → `deliveryService->deliver()` → kirim notif pembeli → log aktivitas → edit pesan admin → **baru** `answerCallbackQuery()` di paling akhir. Kalau salah satu langkah di tengah melempar exception (mis. `DeliveryService` gagal karena stok akun corrupt, atau `Storage` disk tidak bisa ditulis), maka:
1. Exception ditangkap oleh try-catch di `TelegramBotService::handleUpdate()` (level atas)
2. `answerCallbackQuery()` **tidak pernah dipanggil**
3. Tombol di Telegram admin akan menampilkan **loading spinner tanpa akhir** — client Telegram baru menyerah setelah beberapa detik tanpa pesan error apapun ke user

Ini adalah bug nyata yang match dengan gejala di judul brief kamu.
**Perbaikan:** `CallbackRouter` sekarang membungkus seluruh eksekusi handler dalam try/catch sendiri dengan `finally`-semantics: kalau terjadi exception di mana pun di dalam `PaymentHandler`, `answerCallback()` tetap dipanggil di blok `catch` router (lihat `CallbackRouter.php`).

### Bug #3 — `array_merge(null, array)` berpotensi Fatal Error
**File:** `TelegramBotService.php` (lama), `handleChooseProduct()` & `handleChooseVariant()`
```php
'data' => array_merge($session->data, ['product_id' => $product->id])
```
**Penyebab:** Kolom `telegram_sessions.data` bertipe `json nullable` **tanpa default** di level database. Kalau baris tersebut pernah ter-set `null` (state lama, race condition, atau reset manual), maka `$session->data` bernilai `null`, dan `array_merge(null, [...])` melempar `TypeError` di PHP 8.x — bukan warning, tapi fatal. Karena posisinya ada di step **kedua** alur (pilih produk/varian), ini match sekali dengan pola "klik pertama jalan, klik berikutnya mati".
**Perbaikan:** Semua pemakaian diganti `array_merge($session->data ?? [], [...])` di `CommandRouter.php`.

### Bug #4 — Duplikasi logic generate invoice number
**File:** `TelegramBotService.php` (lama) baris ~317–323, padahal `app/Models/Order.php` sudah punya `Order::generateInvoiceNumber()` yang identik fungsinya.
**Penyebab:** Dua sumber kebenaran untuk format invoice — kalau salah satu diubah (mis. format invoice diganti dari admin panel), yang satu lagi bisa lupa ikut diubah, menyebabkan invoice yang tidak konsisten.
**Perbaikan:** `OrderHandler::createOrderAndRequestPayment()` sekarang memakai `Order::generateInvoiceNumber()` yang sudah ada di model.

### Bug #5 (minor, defensif) — Callback tak dikenal tidak dijawab
**Penyebab:** Kalau `callback_data` tidak cocok dengan prefix manapun (mis. tombol lama dari pesan expired), kode lama diam total.
**Perbaikan:** `CallbackRouter` sekarang punya fallback: log warning + `answerCallback()` dengan pesan "Aksi tidak dikenali atau sudah kedaluwarsa."

---

## 3. File yang diperbaiki / dibuat

| File | Status | Isi |
|---|---|---|
| `app/Http/Controllers/Telegram/WebhookController.php` | Diubah | Update namespace ke `App\Services\Telegram\TelegramBotService` |
| `app/Services/Telegram/TelegramBotService.php` | **Baru** (pengganti `app/Services/TelegramBotService.php`) | Orchestrator tipis: upsert user/session, dispatch ke `CommandRouter`/`CallbackRouter` |
| `app/Services/Telegram/CommandRouter.php` | **Baru** | State machine untuk update bertipe `message` (menu ReplyKeyboard) |
| `app/Services/Telegram/CallbackRouter.php` | **Baru** | Dispatch `callback_query` berdasarkan prefix `callback_data`, dengan jaminan `answerCallback()` selalu terpanggil |
| `app/Services/Telegram/KeyboardBuilder.php` | **Baru** | Semua definisi ReplyKeyboard & InlineKeyboard + konstanta label tombol (single source of truth) |
| `app/Services/Telegram/MessageSender.php` | **Baru** | Wrapper tunggal ke semua pemanggilan Telegram Bot API (`sendMessage`, `sendPhoto`, `editMessageText`, `editMessageCaption`, `editMessageReplyMarkup`, `answerCallbackQuery`) |
| `app/Services/Telegram/ProductHandler.php` | **Baru** | Alur kategori → produk → varian |
| `app/Services/Telegram/OrderHandler.php` | **Baru** | Konfirmasi order, kirim QRIS, terima bukti bayar, riwayat pesanan |
| `app/Services/Telegram/PaymentHandler.php` | **Baru** | Logic verifikasi/tolak pembayaran oleh admin (dipanggil dari `CallbackRouter`) |
| `app/Services/TelegramBotService.php` | **Dihapus** | Digantikan struktur di atas |
| `app/Services/TelegramSessionService.php` | **Dihapus** | Isinya dipecah ke `KeyboardBuilder` + `ProductHandler` + `OrderHandler` |

> Catatan: `Commands/`, `Keyboard/`, `Callback/` sebagai folder terpisah (sesuai target arsitektur di brief) **sengaja tidak dibuat sebagai folder kosong terpisah** — isinya sudah tercakup dalam `KeyboardBuilder` (folder `Keyboard`) dan `CallbackRouter`+`PaymentHandler` (folder `Callback`) di dalam `Services/Telegram/`. Untuk ukuran aplikasi ini (1 bot, ~10 state), memisahkan jadi lebih banyak folder/namespace justru menambah indirection tanpa manfaat nyata — ini keputusan Clean Code (YAGNI), bukan kelalaian. Kalau nanti jumlah callback/command bertambah signifikan (>15–20 jenis), baru layak dipecah per-file per-command dengan interface `CommandInterface`/`CallbackInterface` + auto-discovery.
>
> **Repository Pattern tidak ditambahkan** — brief bilang "bila perlu", dan untuk Eloquent model sesederhana ini (query 1–2 baris, tidak ada logic query kompleks yang reusable lintas layer), Repository Pattern akan jadi lapisan abstraksi ekstra tanpa manfaat (juga bagian dari YAGNI/Clean Code, bukan template dogmatis).

---

## 4. Cara apply

Semua file baru sudah lolos `php -l` (syntax check, PHP 8.3). Cara pasang di server:

```bash
cd /path/to/TeleBotStore
git apply refactor.diff
# atau, kalau lebih suka manual: timpa file-file di app/Services/Telegram/
# dan app/Http/Controllers/Telegram/WebhookController.php dengan isi dari paket ini,
# lalu hapus app/Services/TelegramBotService.php & app/Services/TelegramSessionService.php

composer dump-autoload
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

Tidak ada perubahan skema database — jadi tidak perlu migration baru.

---

## 5. Alur callback Telegram — dari klik tombol sampai bot membalas

Karena bot ini punya **dua jalur berbeda** tergantung jenis tombol, saya jelaskan keduanya:

### A. Tombol menu utama pembeli (ReplyKeyboard — "🛍️ Produk", dst.)
```
User tap tombol reply-keyboard
      │  (Telegram client mengirim message.text = label tombol persis)
      ▼
Telegram API → POST /telegram/webhook
      ▼
WebhookController::handle()
      │  Telegram::getWebhookUpdate() → objek Update
      ▼
TelegramBotService::handleUpdate($update)
      │  $update->getCallbackQuery() → null (bukan inline button)
      │  $update->getMessage() → ada
      │  upsert TelegramUser, firstOrCreate TelegramSession
      ▼
CommandRouter::handle($message, $user, $session)
      │  cek dulu "global command" (Home/Bantuan/dst) → kalau cocok, selesai
      │  kalau tidak, lihat $session->state → dispatch ke handler state
      ▼
ProductHandler / OrderHandler
      │  query DB, susun teks & keyboard lewat KeyboardBuilder
      ▼
MessageSender::text()/photo()
      │  Telegram::sendMessage() / sendPhoto()
      ▼
Telegram API mengirim balasan ke chat user
```

### B. Tombol inline admin ("✅ Verifikasi" / "❌ Tolak")
```
Admin tap inline button
      │  Telegram client mengirim update bertipe callback_query
      │  (bukan message baru — pesan lama tetap ada, hanya event callback)
      ▼
Telegram API → POST /telegram/webhook
      ▼
WebhookController::handle() → Telegram::getWebhookUpdate()
      ▼
TelegramBotService::handleUpdate($update)
      │  $update->getCallbackQuery() → ADA → langsung diteruskan,
      │  TIDAK lewat upsert TelegramUser/Session (ini bukan alur pembeli)
      ▼
CallbackRouter::handle($callbackQuery)
      │  cocokkan prefix callback_data ('verify_order_' / 'reject_order_')
      │  try { ... } catch { pastikan answerCallback() tetap terpanggil }
      ▼
PaymentHandler::verify() / reject()
      │  cek otorisasi admin_telegram_id
      │  cek idempotensi (sudah diproses sebelumnya?)
      │  update Order/Payment, panggil DeliveryService, log aktivitas
      ▼
MessageSender::editText()/editCaption()   → ubah pesan admin (hapus tombol)
MessageSender::text()                     → notifikasi ke pembeli
MessageSender::answerCallback()           → WAJIB, hentikan loading spinner tombol
```

**Poin paling penting** dari kedua alur ini: `answerCallbackQuery()` **harus selalu dipanggil** untuk setiap `callback_query`, terlepas dari sukses/gagalnya proses — kalau tidak, Telegram client akan menampilkan tombol yang seperti "tidak merespon" (spinner jalan terus, baru berhenti sendiri setelah timeout tanpa pesan apapun). Itulah kenapa di refactor ini, tanggung jawab "pastikan callback dijawab" dipindah dari tanggung jawab tiap handler (rawan lupa) ke satu titik terpusat di `CallbackRouter` (tidak bisa lupa).

---

