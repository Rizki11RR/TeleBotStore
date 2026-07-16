<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ActivityLogService;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Telegram\Bot\Laravel\Facades\Telegram;

class PaymentController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $logService,
        private readonly DeliveryService $deliveryService
    ) {}

    public function index(Request $request): View
    {
        $query = Payment::with(['order.telegramUser', 'order.productVariant.product', 'latestProof']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();
        $statuses = PaymentStatus::cases();

        return view('admin.payments.index', compact('payments', 'statuses'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['order.telegramUser', 'order.productVariant.product', 'proofs', 'verifier']);
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Payment $payment): RedirectResponse
    {
        if ($payment->status !== PaymentStatus::WAITING) {
            return back()->with('error', 'Pembayaran tidak dalam status menunggu verifikasi.');
        }

        $payment->update([
            'status'      => PaymentStatus::VERIFIED,
            'verified_at' => now(),
            'verified_by' => auth('admin')->id(),
        ]);

        $order = $payment->order;

        // Kirim notifikasi ke user Telegram
        try {
            Telegram::sendMessage([
                'chat_id'    => $order->telegramUser->telegram_id,
                'text'       => "✅ *PEMBAYARAN DIVERIFIKASI*\n\nPembayaran untuk invoice `{$order->invoice_number}` senilai *Rp" . number_format($payment->amount, 0, ',', '.') . "* telah diverifikasi dan diterima. Terima kasih!\n\nProduk akan segera dikirimkan.",
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            // Abaikan error kirim pesan agar status tetap ter-update
        }

        // Jalankan pengiriman produk otomatis
        $this->deliveryService->deliver($order);

        $this->logService->log('payment.verify', "Memverifikasi pembayaran untuk order {$order->invoice_number}", $payment);

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Pembayaran berhasil diverifikasi. Produk sedang/telah dikirim.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($payment->status !== PaymentStatus::WAITING) {
            return back()->with('error', 'Pembayaran tidak dalam status menunggu verifikasi.');
        }

        $payment->update([
            'status'           => PaymentStatus::REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'verified_at'      => now(),
            'verified_by'      => auth('admin')->id(),
        ]);

        $order = $payment->order;
        $order->update(['status' => OrderStatus::WAITING_PAYMENT]);

        // Kirim notifikasi penolakan ke user Telegram
        try {
            Telegram::sendMessage([
                'chat_id'    => $order->telegramUser->telegram_id,
                'text'       => "❌ *PEMBAYARAN DITOLAK*\n\nPembayaran untuk invoice `{$order->invoice_number}` ditolak oleh admin.\n\n*Alasan Penolakan:*\n{$request->rejection_reason}\n\nSilakan lakukan pembayaran ulang dan upload bukti pembayaran yang valid.",
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            // Abaikan error kirim pesan
        }

        $this->logService->log('payment.reject', "Menolak pembayaran untuk order {$order->invoice_number}. Alasan: {$request->rejection_reason}", $payment);

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Pembayaran berhasil ditolak.');
    }
}
