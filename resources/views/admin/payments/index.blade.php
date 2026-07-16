@extends('layouts.app')

@section('title', 'Daftar Pembayaran')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Verifikasi Pembayaran</h3>
                <p class="text-subtitle text-muted">Daftar transaksi pembayaran yang memerlukan verifikasi manual.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
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
                <h4 class="card-title mb-3">Filter Status Pembayaran</h4>
                <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
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
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
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
                                <th>Jumlah Bayar</th>
                                <th>Status</th>
                                <th>Bukti Terakhir</th>
                                <th>Tanggal Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>
                                        @if ($payment->order)
                                            <a href="{{ route('admin.orders.show', $payment->order) }}" class="fw-bold">
                                                {{ $payment->order->invoice_number }}
                                                @if ($payment->order->trashed())
                                                    <span class="text-danger small">(Dihapus)</span>
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-muted fw-bold">[Pesanan Dihapus]</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($payment->order && $payment->order->telegramUser)
                                            {{ $payment->order->telegramUser->full_name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="{{ $payment->status->badge() }}">
                                            {{ $payment->status->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($payment->latestProof)
                                            <span class="badge bg-light-info text-info"><i class="bi bi-image"></i> Ada Bukti</span>
                                        @else
                                            <span class="badge bg-light-danger text-danger">Belum Upload</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->updated_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye-fill me-1"></i> Periksa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Tidak ditemukan transaksi pembayaran.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
                    </div>
                    <div>
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
