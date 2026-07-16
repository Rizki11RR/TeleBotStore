<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use App\Services\BroadcastService;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastController extends Controller
{
    public function __construct(
        private readonly BroadcastService $broadcastService,
        private readonly ActivityLogService $logService
    ) {}

    public function index(): View
    {
        $allUsersCount    = TelegramUser::where('is_blocked', false)->count();
        $activeUsersCount = TelegramUser::where('is_blocked', false)->whereHas('orders')->count();

        return view('admin.broadcast.index', compact('allUsersCount', 'activeUsersCount'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:4096',
            'target'  => 'required|in:all,active',
        ]);

        $count = $this->broadcastService->send($request->message, $request->target);

        $targetLabel = $request->target === 'all' ? 'Semua User' : 'User Aktif';
        $this->logService->log('broadcast.send', "Mengirim broadcast pesan ke {$count} penerima ({$targetLabel})");

        return redirect()->route('admin.broadcast.index')
            ->with('success', "Broadcast berhasil dikirim ke {$count} user via queue.");
    }
}
