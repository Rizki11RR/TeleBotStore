@extends('layouts.app')

@section('title', 'Detail User Telegram')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Detail User Telegram: {{ $telegramUser->full_name }}</h3>
                <p class="text-subtitle text-muted">Informasi profile Telegram dan riwayat pembelian.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.telegram-users.index') }}">User Telegram</a></li>
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
            <div class="col-md-4 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Informasi Akun</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Nama Depan</span>
                                <strong>{{ $telegramUser->first_name }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Nama Belakang</span>
                                <strong>{{ $telegramUser->last_name ?: '-' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Username</span>
                                <strong>@`{{ $telegramUser->username ?: 'no_username' }}`</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Telegram ID</span>
                                <code>{{ $telegramUser->telegram_id }}</code>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Sesi Bot Saat Ini</span>
                                <span class="badge bg-light-info text-info">{{ $telegramUser->session ? $telegramUser->session->state : 'IDLE' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Status Bot</span>
                                @if ($telegramUser->is_blocked)
                                    <span class="badge bg-danger">Diblokir</span>
                                @else
                                    <span class="badge bg-success">Aktif</span>
                                @endif
                            </li>
                        </ul>

                        <div class="d-flex gap-2 mt-4 justify-content-end">
                            @if ($telegramUser->is_blocked)
                                <form action="{{ route('admin.telegram-users.toggle-block', $telegramUser) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-unlock-fill me-1"></i> Buka Blokir
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.telegram-users.toggle-block', $telegramUser) }}" method="POST" class="confirm-form"
                                      data-confirm="Apakah Anda yakin ingin memblokir user ini? Bot tidak akan merespon perintah user."
                                      data-confirm-title="Blokir User Telegram"
                                      data-confirm-button="Ya, Blokir!"
                                      data-confirm-color="#dc3545"
                                      data-confirm-icon="warning">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="bi bi-slash-circle-fill me-1"></i> Blokir User
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.telegram-users.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Riwayat Transaksi Pesanan</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Produk & Varian</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($telegramUser->orders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold">
                                                    {{ $order->invoice_number }}
                                                </a>
                                            </td>
                                            <td>{{ $order->productVariant->product->name }} - {{ $order->productVariant->name }}</td>
                                            <td>Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="{{ $order->status->badge() }}">
                                                    {{ $order->status->label() }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                Belum ada transaksi pesanan.
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
    </section>
</div>
@endsection
