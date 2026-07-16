<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Tampilkan halaman dashboard dengan statistik. */
    public function index(): View
    {
        // Statistik utama
        $totalUsers    = TelegramUser::count();
        $totalOrders   = Order::count();
        $totalProducts = Product::count();

        // Revenue
        $revenueToday = Order::where('status', 'COMPLETED')
            ->whereDate('created_at', today())
            ->sum('total_price');

        $revenueThisMonth = Order::where('status', 'COMPLETED')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');

        // Produk terlaris (top 5)
        $topProducts = DB::table('orders')
            ->join('product_variants', 'orders.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('COUNT(orders.id) as total_orders'), DB::raw('SUM(orders.total_price) as total_revenue'))
            ->where('orders.status', 'COMPLETED')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();

        // Grafik penjualan 7 hari terakhir
        $salesChart = Order::where('status', 'COMPLETED')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Isi hari yang tidak ada datanya dengan 0
        $chartLabels = [];
        $chartData   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartData[]   = (float) ($salesChart[$date]->total ?? 0);
        }

        // Order terbaru
        $recentOrders = Order::with(['telegramUser', 'productVariant.product'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'totalOrders',
            'totalProducts',
            'revenueToday',
            'revenueThisMonth',
            'topProducts',
            'chartLabels',
            'chartData',
            'recentOrders',
        ));
    }
}
