<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TelegramUserController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(Request $request): View
    {
        $query = TelegramUser::withCount('orders');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('telegram_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_blocked', $request->status === 'blocked');
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.telegram-users.index', compact('users'));
    }

    public function show(TelegramUser $telegramUser): View
    {
        $telegramUser->load(['orders.productVariant.product', 'session']);
        return view('admin.telegram-users.show', compact('telegramUser'));
    }

    public function toggleBlock(TelegramUser $telegramUser): RedirectResponse
    {
        $telegramUser->update([
            'is_blocked' => !$telegramUser->is_blocked,
        ]);

        $status = $telegramUser->is_blocked ? 'diblokir' : 'dibuka blokirnya';
        $action = $telegramUser->is_blocked ? 'telegram_user.block' : 'telegram_user.unblock';

        $this->logService->log($action, "Mengubah status user Telegram {$telegramUser->full_name} menjadi {$status}", $telegramUser);

        return redirect()->route('admin.telegram-users.show', $telegramUser)
            ->with('success', "Status user Telegram berhasil diubah menjadi {$status}.");
    }
}
