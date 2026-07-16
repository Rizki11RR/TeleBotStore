<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        $validated = $request->validate([
            'store_name'          => 'required|string|max:100',
            'store_description'   => 'nullable|string|max:250',
            'admin_telegram_id'   => 'nullable|string|max:50',
            'bot_welcome_message' => 'required|string|max:1000',
            'bot_help_message'    => 'required|string|max:1000',
            'bot_payment_info'    => 'required|string|max:1000',
            'bot_contact_admin'   => 'required|string|max:1000',
        ]);

        foreach ($validated as $key => $value) {
            $group = in_array($key, ['store_name', 'store_description', 'admin_telegram_id']) ? 'general' : 'bot';
            Setting::set($key, $value, $group);
        }

        $this->logService->log('settings.update', 'Memperbarui pengaturan umum dan bot Telegram');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
