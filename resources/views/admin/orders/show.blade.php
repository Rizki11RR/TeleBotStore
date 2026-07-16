@extends('layouts.app')

@section('title', 'Detail Order ' . $order->invoice_number)

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Detail Pesanan: {{ $order->invoice_number }}</h3>
                <p class="text-subtitle text-muted">Lihat rincian lengkap order dan ubah status.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Pesanan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Informasi Pembeli & Produk</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th class="w-30">User Telegram</th>
                                <td>
                                    <strong>{{ $order->telegramUser->full_name }}</strong><br>
                                    ID: <code>{{ $order->telegramUser->telegram_id }}</code><br>
                                    Username: @`{{ $order->telegramUser->username ?: 'no_username' }}`
                                </td>
                            </tr>
                            <tr>
                                <th>Produk</th>
                                <td>
                                    <strong>{{ $order->productVariant->product->name }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Varian</th>
                                <td>{{ $order->productVariant->name }}</td>
                            </tr>
                            <tr>
                                <th>Kuantitas</th>
                                <td>{{ $order->quantity }} pcs</td>
                            </tr>
                            <tr>
                                <th>Total Harga</th>
                                <td class="fs-5 fw-bold text-success">
                                    Rp{{ number_format($order->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Transaksi</th>
                                <td>{{ $order->created_at->format('d F Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Status & Catatan</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold">Ubah Status Pesanan</label>
                                <select id="status" name="status" class="form-select">
                                    @foreach (\App\Enums\OrderStatus::cases() as $status)
                                        <option value="{{ $status->value }}" {{ $order->status === $status ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label fw-bold">Catatan Pesanan / Buyer</label>
                                <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $order->notes) }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                @if (!in_array($order->status, [\App\Enums\OrderStatus::COMPLETED, \App\Enums\OrderStatus::CANCELLED]))
                                    <button type="submit" name="status" value="CANCELLED" class="btn btn-danger">Batalkan Order</button>
                                @endif
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if ($order->payment)
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Pembayaran Terkait</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Jumlah Bayar</th>
                                        <th>Status Pembayaran</th>
                                        <th>Tanggal Kirim Bukti</th>
                                        <th>Diverifikasi Oleh</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Rp{{ number_format($order->payment->amount, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="{{ $order->payment->status->badge() }}">
                                                {{ $order->payment->status->label() }}
                                            </span>
                                        </td>
                                        <td>{{ $order->payment->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $order->payment->verifier ? $order->payment->verifier->name : '-' }}</td>
                                        <td>
                                            <a href="{{ route('admin.payments.show', $order->payment) }}" class="btn btn-info btn-sm">
                                                Detail Bukti & Verifikasi
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
