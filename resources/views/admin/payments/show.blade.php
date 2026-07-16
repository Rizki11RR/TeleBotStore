@extends('layouts.app')

@section('title', 'Detail Verifikasi Pembayaran')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Verifikasi Pembayaran: {{ $payment->order->invoice_number }}</h3>
                <p class="text-subtitle text-muted">Periksa kesesuaian nominal dan bukti transfer QRIS pembeli.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Pembayaran</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Verifikasi</li>
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
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="card-title">Rincian Pembayaran</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>Invoice</th>
                                <td>
                                    <a href="{{ route('admin.orders.show', $payment->order) }}" class="fw-bold">
                                        {{ $payment->order->invoice_number }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>User Telegram</th>
                                <td>{{ $payment->order->telegramUser->full_name }}</td>
                            </tr>
                            <tr>
                                <th>Varian Produk</th>
                                <td>{{ $payment->order->productVariant->product->name }} - {{ $payment->order->productVariant->name }}</td>
                            </tr>
                            <tr>
                                <th>Nominal yang Harus Dibayar</th>
                                <td class="fs-5 fw-bold text-primary">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Status Pembayaran</th>
                                <td>
                                    <span class="{{ $payment->status->badge() }}">
                                        {{ $payment->status->label() }}
                                    </span>
                                </td>
                            </tr>
                            @if ($payment->verified_by)
                                <tr>
                                    <th>Diverifikasi Oleh</th>
                                    <td>{{ $payment->verifier->name }} pada {{ $payment->verified_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endif
                            @if ($payment->rejection_reason)
                                <tr>
                                    <th class="text-danger">Alasan Penolakan</th>
                                    <td class="text-danger fw-bold">{{ $payment->rejection_reason }}</td>
                                </tr>
                            @endif
                        </table>

                        @if ($payment->status === \App\Enums\PaymentStatus::WAITING)
                            <div class="d-flex gap-2 mt-4">
                                <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="d-inline confirm-form"
                                      data-confirm="Apakah Anda yakin ingin menyetujui pembayaran ini? Produk digital akan langsung dikirim."
                                      data-confirm-title="Setujui Pembayaran"
                                      data-confirm-button="Ya, Setujui!"
                                      data-confirm-color="#198754"
                                      data-confirm-icon="success">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="bi bi-check-circle-fill me-1"></i> Setujui Pembayaran
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle-fill me-1"></i> Tolak Pembayaran
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="card-title">Gambar Bukti Transfer</h4>
                    </div>
                    <div class="card-body text-center">
                        @if ($payment->proofs->isNotEmpty())
                            @foreach ($payment->proofs as $proof)
                                <div class="mb-3">
                                    <span class="d-block small text-muted mb-1">Diupload pada: {{ $proof->uploaded_at->format('d F Y H:i') }}</span>
                                    <img src="{{ asset('storage/' . $proof->file_path) }}" alt="Bukti Transfer" class="img-fluid rounded border p-2 img-thumbnail" style="max-height: 450px;">
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i> Pembeli belum mengunggah foto bukti pembayaran.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Reject Payment Modal -->
<div class="modal fade text-left" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel1">Tolak Pembayaran</h5>
                <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form action="{{ route('admin.payments.reject', $payment) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Silakan berikan alasan penolakan pembayaran ini agar pembeli mengetahuinya di Telegram:</p>
                    <div class="form-group">
                        <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Contoh: Bukti transfer buram / nominal transfer tidak sesuai..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Batal</span>
                    </button>
                    <button type="submit" class="btn btn-danger ml-1">
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Tolak</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
