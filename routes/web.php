<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\DigitalFileController;
use App\Http\Controllers\Admin\QrisController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TelegramUserController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Telegram\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // Guest-only routes (login form)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Protected Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin.auth')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Katalog
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('variants', VariantController::class)->except(['index']);
        Route::get('variants', [VariantController::class, 'index'])->name('variants.index');
        Route::resource('digital-files', DigitalFileController::class);

        // Pembayaran
        Route::get('qris', [QrisController::class, 'index'])->name('qris.index');
        Route::put('qris', [QrisController::class, 'update'])->name('qris.update');

        // Transaksi
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        Route::resource('payments', PaymentController::class)->only(['index', 'show']);
        Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::patch('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

        // Telegram
        Route::resource('telegram-users', TelegramUserController::class)->only(['index', 'show']);
        Route::patch('telegram-users/{telegramUser}/toggle-block', [TelegramUserController::class, 'toggleBlock'])
            ->name('telegram-users.toggle-block');

        Route::get('broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('broadcast', [BroadcastController::class, 'send'])->name('broadcast.send');

        // Sistem
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::resource('admins', AdminController::class)->except(['show']);
    });
});

/*
|--------------------------------------------------------------------------
| Telegram Webhook
|--------------------------------------------------------------------------
*/

Route::post('/telegram/webhook', [WebhookController::class, 'handle'])
    ->name('telegram.webhook');

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});
