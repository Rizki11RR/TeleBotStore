<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(Request $request): View
    {
        $query = Order::with(['telegramUser', 'productVariant.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('telegramUser', function ($qu) use ($search) {
                      $qu->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $statuses = OrderStatus::cases();

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order): View
    {
        $order->load(['telegramUser', 'productVariant.product', 'payment.proofs']);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
            'notes'  => 'nullable|string',
        ]);

        $oldStatus = $order->status->value;
        $order->update([
            'status' => $request->status,
            'notes'  => $request->notes,
        ]);

        $this->logService->log(
            'order.update',
            "Mengubah status order {$order->invoice_number} dari {$oldStatus} ke {$request->status}",
            $order
        );

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status order berhasil diperbarui.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        if (in_array($order->status, [OrderStatus::COMPLETED, OrderStatus::CANCELLED])) {
            return back()->with('error', 'Order yang sudah selesai atau batal tidak dapat dibatalkan.');
        }

        $order->update(['status' => OrderStatus::CANCELLED]);

        $this->logService->log('order.cancel', "Membatalkan order {$order->invoice_number}", $order);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order berhasil dibatalkan.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $invoice = $order->invoice_number;
        $order->delete();

        $this->logService->log('order.delete', "Menghapus order {$invoice}", $order);

        return redirect()->route('admin.orders.index')
            ->with('success', "Order {$invoice} berhasil dihapus.");
    }
}
