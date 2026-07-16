<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            'qris_image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'account_name' => 'required|string|max:100',
        ]);

        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('qris', 'public');
            Setting::set('qris_image_path', $path, 'payment');
        }

        Setting::set('qris_account_name', $request->account_name, 'payment');

        $this->logService->log('qris.update', 'Memperbarui gambar/pengaturan QRIS');

        return redirect()->route('admin.qris.index')
            ->with('success', 'QRIS berhasil diperbarui.');
    }
}
