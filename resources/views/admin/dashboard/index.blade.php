@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
@endpush

@section('content')
<div class="page-heading">
    <h3>Dashboard</h3>
</div>

<div class="page-content">
    {{-- Stat Cards --}}
    <section class="row">
        <div class="col-12 col-lg-9">
            <div class="row">
                {{-- Total User --}}
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon purple mb-2">
                                        <i class="bi bi-people-fill fs-5"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Total User</h6>
                                    <h6 class="font-extrabold mb-0">{{ number_format($totalUsers) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Order --}}
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon blue mb-2">
                                        <i class="bi bi-cart-fill fs-5"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Total Order</h6>
                                    <h6 class="font-extrabold mb-0">{{ number_format($totalOrders) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Produk --}}
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon green mb-2">
                                        <i class="bi bi-box-seam-fill fs-5"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Total Produk</h6>
                                    <h6 class="font-extrabold mb-0">{{ number_format($totalProducts) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Revenue Hari Ini --}}
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon red mb-2">
                                        <i class="bi bi-currency-dollar fs-5"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Revenue Hari Ini</h6>
                                    <h6 class="font-extrabold mb-0">Rp{{ number_format($revenueToday, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Revenue Bulan Ini + Grafik --}}
            <div class="row">
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon green me-3">
                                    <i class="bi bi-graph-up-arrow fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold mb-0">Revenue Bulan Ini</h6>
                                    <h4 class="font-extrabold mb-0 text-success">
                                        Rp{{ number_format($revenueThisMonth, 0, ',', '.') }}
                                    </h4>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ now()->translatedFormat('F Y') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Grafik Penjualan (7 Hari)</h4>
                        </div>
                        <div class="card-body">
                            <div id="chart-sales"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Terbaru --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Order Terbaru</h4>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>User</th>
                                            <th>Produk</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentOrders as $order)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.orders.show', $order) }}"
                                                       class="text-decoration-none fw-bold">
                                                        {{ $order->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $order->telegramUser->full_name }}</td>
                                                <td>
                                                    {{ $order->productVariant->product->name ?? '-' }}
                                                    <br>
                                                    <small class="text-muted">{{ $order->productVariant->name }}</small>
                                                </td>
                                                <td>{{ $order->formatted_total_price }}</td>
                                                <td>
                                                    <span class="{{ $order->status->badgeClass() }}">
                                                        {{ $order->status->label() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $order->created_at->diffForHumans() }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    Belum ada order
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Produk Terlaris --}}
        <div class="col-12 col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4>🏆 Produk Terlaris</h4>
                </div>
                <div class="card-body p-0">
                    @forelse ($topProducts as $index => $product)
                        <div class="d-flex align-items-center px-4 py-3 border-bottom">
                            <div class="me-3">
                                <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light text-dark') }} rounded-circle p-2">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold small">{{ $product->name }}</h6>
                                <small class="text-muted">{{ $product->total_orders }} order</small>
                            </div>
                            <div class="text-end">
                                <small class="text-success fw-bold">
                                    Rp{{ number_format($product->total_revenue, 0, ',', '.') }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            Belum ada data
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="card">
                <div class="card-header">
                    <h4>Info Admin</h4>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md me-3 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:48px;height:48px;">
                            <i class="bi bi-person-badge text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">{{ auth('admin')->user()->name }}</h6>
                            <small class="text-muted">{{ auth('admin')->user()->email }}</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-gear me-1"></i> Settings
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
<script>
    // Grafik Penjualan 7 Hari
    const chartOptions = {
        chart: { type: 'area', height: 200, toolbar: { show: false } },
        series: [{ name: 'Revenue', data: {!! json_encode($chartData) !!} }],
        xaxis: { categories: {!! json_encode($chartLabels) !!} },
        yaxis: {
            labels: {
                formatter: (val) => 'Rp' + new Intl.NumberFormat('id-ID').format(val)
            }
        },
        colors: ['#6366f1'],
        fill: { type: 'gradient', gradient: { opacityFrom: 0.6, opacityTo: 0.1 } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        tooltip: {
            y: {
                formatter: (val) => 'Rp' + new Intl.NumberFormat('id-ID').format(val)
            }
        }
    };

    const chart = new ApexCharts(document.querySelector('#chart-sales'), chartOptions);
    chart.render();
</script>
@endpush
