@extends('layouts.app')

@section('title', 'Metode Pembayaran QRIS')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Pengaturan QRIS</h3>
                <p class="text-subtitle text-muted">Kelola gambar QRIS dan nama akun pembayaran.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">QRIS</li>
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
                        <h4 class="card-title">Upload QRIS</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.qris.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="account_name" class="form-label fw-bold">Nama Akun Pembayaran <span class="text-danger">*</span></label>
                                <input type="text" id="account_name" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                                       value="{{ old('account_name', $accountName) }}" required>
                                <div class="form-text small">Nama merchant QRIS (misal: Nexora Digital Store).</div>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="qris_image" class="form-label fw-bold">Gambar QRIS <span class="text-danger">*</span></label>
                                <input type="file" id="qris_image" name="qris_image" class="form-control @error('qris_image') is-invalid @enderror">
                                <div class="form-text small">Unggah gambar QRIS format JPG/PNG, maksimal 2MB.</div>
                                @error('qris_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Preview QRIS Saat Ini</h4>
                    </div>
                    <div class="card-body text-center">
                        @if ($qrisImage)
                            <img src="{{ asset('storage/' . $qrisImage) }}" alt="QRIS Merchant" class="img-fluid rounded border p-2" style="max-height: 400px;">
                            <div class="mt-2 fw-bold text-success">{{ $accountName }}</div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i> QRIS belum diunggah. Pembeli di Telegram tidak akan bisa melihat metode pembayaran QRIS.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
