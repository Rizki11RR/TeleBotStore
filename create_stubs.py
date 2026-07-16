#!/usr/bin/env python3
import os

base_path = '/home/rizki/BotTeleStore/nexora-digital/app/Http/Controllers/Admin'
os.makedirs(base_path, exist_ok=True)

controllers = [
    ('ProductController', 'product', 'products', 'Product', 'App\\Models\\Product'),
    ('VariantController', 'product_variant', 'variants', 'ProductVariant', 'App\\Models\\ProductVariant'),
    ('DigitalFileController', 'digital_file', 'digital-files', 'DigitalFile', 'App\\Models\\DigitalFile'),
    ('OrderController', 'order', 'orders', 'Order', 'App\\Models\\Order'),
    ('PaymentController', 'payment', 'payments', 'Payment', 'App\\Models\\Payment'),
    ('TelegramUserController', 'telegram_user', 'telegram-users', 'TelegramUser', 'App\\Models\\TelegramUser'),
    ('AdminController', 'admin', 'admins', 'Admin', 'App\\Models\\Admin'),
]

for name, var, route, model, model_ns in controllers:
    route_dot = route.replace('-', '-')
    content = f"""<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use {model_ns};
use Illuminate\\Http\\Request;
use Illuminate\\View\\View;
use Illuminate\\Http\\RedirectResponse;

class {name} extends Controller
{{
    public function index(): View
    {{
        ${var}s = {model}::latest()->paginate(15);
        return view('admin.{route}.index', compact('{var}s'));
    }}

    public function show({model} ${var}): View
    {{
        return view('admin.{route}.show', compact('{var}'));
    }}

    public function create(): View
    {{
        return view('admin.{route}.create');
    }}

    public function store(Request $request): RedirectResponse
    {{
        return redirect()->route('admin.{route}.index')->with('success', 'Data berhasil disimpan.');
    }}

    public function edit({model} ${var}): View
    {{
        return view('admin.{route}.edit', compact('{var}'));
    }}

    public function update(Request $request, {model} ${var}): RedirectResponse
    {{
        return redirect()->route('admin.{route}.index')->with('success', 'Data berhasil diperbarui.');
    }}

    public function destroy({model} ${var}): RedirectResponse
    {{
        ${var}->delete();
        return redirect()->route('admin.{route}.index')->with('success', 'Data berhasil dihapus.');
    }}
}}
"""
    filepath = os.path.join(base_path, f'{name}.php')
    if not os.path.exists(filepath):
        with open(filepath, 'w') as f:
            f.write(content)
        print(f'Created: {name}.php')
    else:
        print(f'Skipped: {name}.php')

# QrisController
qris = """<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Setting;
use App\\Services\\ActivityLogService;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\View\\View;

class QrisController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(): View
    {
        $qrisImage = Setting::get('qris_image_path');
        $accountName = Setting::get('qris_account_name', 'Nexora Digital');
        return view('admin.qris.index', compact('qrisImage', 'accountName'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'qris_image'   => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'account_name' => 'required|string|max:100',
        ]);

        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('qris', 'public');
            Setting::set('qris_image_path', $path, 'payment');
        }

        Setting::set('qris_account_name', $request->account_name, 'payment');
        $this->logService->log('qris.update', 'Memperbarui gambar QRIS');

        return redirect()->route('admin.qris.index')->with('success', 'QRIS berhasil diperbarui.');
    }
}
"""

broadcast = """<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\TelegramUser;
use App\\Services\\BroadcastService;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\View\\View;

class BroadcastController extends Controller
{
    public function __construct(private readonly BroadcastService $broadcastService) {}

    public function index(): View
    {
        $userCount = TelegramUser::where('is_blocked', false)->count();
        return view('admin.broadcast.index', compact('userCount'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:4096',
            'target'  => 'required|in:all,active',
        ]);
        $count = $this->broadcastService->send($request->message, $request->target);
        return redirect()->route('admin.broadcast.index')->with('success', "Pesan berhasil dikirim ke {$count} user.");
    }
}
"""

settings_ctrl = """<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Setting;
use App\\Services\\ActivityLogService;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\View\\View;

class SettingController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(): View
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::set($key, $value);
        }
        $this->logService->log('settings.update', 'Memperbarui pengaturan aplikasi');
        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
"""

activity_ctrl = """<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\ActivityLog;
use Illuminate\\View\\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $logs = ActivityLog::with('admin')->latest()->paginate(20);
        return view('admin.activity-logs.index', compact('logs'));
    }
}
"""

for filename, content in [
    ('QrisController.php', qris),
    ('BroadcastController.php', broadcast),
    ('SettingController.php', settings_ctrl),
    ('ActivityLogController.php', activity_ctrl),
]:
    filepath = os.path.join(base_path, filename)
    if not os.path.exists(filepath):
        with open(filepath, 'w') as f:
            f.write(content)
        print(f'Created: {filename}')
    else:
        print(f'Skipped: {filename}')

# BroadcastService and TelegramBotService stubs
services_path = '/home/rizki/BotTeleStore/nexora-digital/app/Services'
os.makedirs(services_path, exist_ok=True)

broadcast_svc = """<?php

namespace App\\Services;

use App\\Jobs\\BroadcastMessage;
use App\\Models\\TelegramUser;

class BroadcastService
{
    /**
     * Kirim broadcast ke semua atau user aktif.
     * @return int Jumlah user yang dikirim
     */
    public function send(string $message, string $target = 'all'): int
    {
        $query = TelegramUser::where('is_blocked', false);

        $users = $query->get();

        foreach ($users as $user) {
            BroadcastMessage::dispatch($user->telegram_id, $message);
        }

        return $users->count();
    }
}
"""

telegram_svc = """<?php

namespace App\\Services;

use App\\Models\\Order;
use App\\Models\\TelegramSession;
use App\\Models\\TelegramUser;
use Telegram\\Bot\\Objects\\Update;

class TelegramBotService
{
    /**
     * Handle incoming Telegram update.
     * Delegasikan ke state handler berdasarkan sesi user.
     */
    public function handleUpdate(Update $update): void
    {
        $message = $update->getMessage();
        if (! $message) {
            return;
        }

        $from = $message->getFrom();
        if (! $from) {
            return;
        }

        // Upsert telegram user
        $user = TelegramUser::updateOrCreate(
            ['telegram_id' => $from->getId()],
            [
                'username'   => $from->getUsername(),
                'first_name' => $from->getFirstName(),
                'last_name'  => $from->getLastName(),
            ]
        );

        if ($user->is_blocked) {
            return;
        }

        // Ambil atau buat sesi
        $session = TelegramSession::firstOrCreate(
            ['telegram_user_id' => $user->id],
            ['state' => 'MENU', 'data' => []]
        );

        $text = $message->getText() ?? '';

        // Route ke handler berdasarkan teks/command
        $this->route($user, $session, $text, $message);
    }

    private function route(TelegramUser $user, TelegramSession $session, string $text, $message): void
    {
        // TODO: Implement full bot flow di Tahap 11
        // Placeholder response
        $sessionService = app(TelegramSessionService::class);
        $sessionService->sendMenu($user->telegram_id);
    }
}
"""

telegram_session_svc = """<?php

namespace App\\Services;

use App\\Models\\Setting;
use Telegram\\Bot\\Laravel\\Facades\\Telegram;

class TelegramSessionService
{
    /** Kirim menu utama ke user. */
    public function sendMenu(int $chatId): void
    {
        $storeName = Setting::get('store_name', 'Nexora Digital');

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => "Halo! Selamat datang di *{$storeName}* 🛍️\\n\\nSilakan pilih menu:",
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => '🏠 Home'], ['text' => '🛍 Produk']],
                    [['text' => '📦 Pesanan Saya'], ['text' => '💳 Cara Pembayaran']],
                    [['text' => '☎ Hubungi Admin'], ['text' => '❓ Bantuan']],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
"""

for filename, content in [
    ('BroadcastService.php', broadcast_svc),
    ('TelegramBotService.php', telegram_svc),
    ('TelegramSessionService.php', telegram_session_svc),
]:
    filepath = os.path.join(services_path, filename)
    if not os.path.exists(filepath):
        with open(filepath, 'w') as f:
            f.write(content)
        print(f'Created service: {filename}')
    else:
        print(f'Skipped service: {filename}')

# BroadcastMessage Job stub
jobs_path = '/home/rizki/BotTeleStore/nexora-digital/app/Jobs'
os.makedirs(jobs_path, exist_ok=True)
broadcast_job = """<?php

namespace App\\Jobs;

use Illuminate\\Bus\\Queueable;
use Illuminate\\Contracts\\Queue\\ShouldQueue;
use Illuminate\\Foundation\\Bus\\Dispatchable;
use Illuminate\\Queue\\InteractsWithQueue;
use Illuminate\\Queue\\SerializesModels;
use Telegram\\Bot\\Laravel\\Facades\\Telegram;

class BroadcastMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly int    $chatId,
        private readonly string $message
    ) {}

    public function handle(): void
    {
        Telegram::sendMessage([
            'chat_id'    => $this->chatId,
            'text'       => $this->message,
            'parse_mode' => 'Markdown',
        ]);
    }
}
"""
with open(os.path.join(jobs_path, 'BroadcastMessage.php'), 'w') as f:
    f.write(broadcast_job)
print('Created job: BroadcastMessage.php')

print('All done!')
