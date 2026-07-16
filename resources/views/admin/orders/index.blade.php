@extends('layouts.app')

@section('title', 'Daftar Order / Pesanan')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Kelola Pesanan</h3>
                <p class="text-subtitle text-muted">Daftar semua transaksi pesanan dari Telegram Bot.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pesanan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="card-title mb-3">Filter & Cari Pesanan</h4>
                <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari invoice atau nama user..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
            <div class="card-body mt-4">
                <div class="table-responsive">
                    <table class="table table-hover table-lg">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>User Telegram</th>
                                <th>Produk & Varian</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-primary">
                                            {{ $order->invoice_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $order->telegramUser->full_name }}<br>
                                        <span class="small text-muted">@`{{ $order->telegramUser->username ?: 'no_username' }}`</span>
                                    </td>
                                    <td>
                                        <strong>{{ $order->productVariant->product->name }}</strong><br>
                                        <span class="small text-muted">{{ $order->productVariant->name }} x {{ $order->quantity }}</span>
                                    </td>
                                    <td class="fw-bold">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="{{ $order->status->badge() }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="delete-form"
                                                  data-confirm="Apakah Anda yakin ingin menghapus data order ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Tidak ditemukan transaksi pesanan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} entries
                    </div>
                    <div>
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
