@extends('layouts.app')

@section('title', 'Kelola Stok Akun: ' . $variant->name)

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Stok Kredensial Akun: {{ $variant->name }}</h3>
                <p class="text-subtitle text-muted">Produk: <a href="{{ route('admin.products.show', $variant->product) }}">{{ $variant->product->name }}</a></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produk</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.show', $variant->product) }}">Detail</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Stok Akun</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="row">
            {{-- Bulk Import Form --}}
            <div class="col-md-5 col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="card-title text-white mb-0"><i class="bi bi-cloud-upload-fill me-2"></i> Bulk Import Kredensial</h4>
                    </div>
                    <div class="card-body pt-3">
                        <form action="{{ route('admin.variants.accounts.store', $variant) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="accounts_data" class="form-label fw-bold">Data Akun <span class="text-danger">*</span></label>
                                <textarea id="accounts_data" name="accounts_data" class="form-control" rows="8" 
                                          placeholder="Format: username/email & password&#10;Contoh:&#10;user1@email.com:password123&#10;user2@email.com|password456&#10;user3@email.com:password789" required></textarea>
                                <div class="form-text small mt-2">
                                    💡 Gunakan pemisah titik dua (<code>:</code>) atau garis tegak (<code>|</code>). Satu akun per baris.
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-primary fw-bold">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kredensial
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Credentials List --}}
            <div class="col-md-7 col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h4 class="card-title mb-0"><i class="bi bi-key-fill me-2"></i> Daftar Kredensial</h4>
                        <span class="badge bg-success fs-6 px-3 py-2">Tersedia: {{ $variant->accounts->where('is_sold', false)->count() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover align-middle">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Username / Email</th>
                                        <th>Password</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($variant->accounts as $acc)
                                        <tr>
                                            <td class="text-nowrap font-monospace small">{{ $acc->username_email }}</td>
                                            <td class="text-nowrap font-monospace small"><code>{{ $acc->password }}</code></td>
                                            <td>
                                                @if ($acc->is_sold)
                                                    <span class="badge bg-light-danger text-danger">
                                                        <i class="bi bi-cart-check-fill me-1"></i> Terjual
                                                    </span>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        {{ $acc->sold_at ? \Carbon\Carbon::parse($acc->sold_at)->format('d/m/Y H:i') : '' }}
                                                    </div>
                                                @else
                                                    <span class="badge bg-light-success text-success">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Tersedia
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if (!$acc->is_sold)
                                                    <form action="{{ route('admin.variants.accounts.destroy', [$variant, $acc]) }}" method="POST" class="delete-form" 
                                                          data-confirm="Apakah Anda yakin ingin menghapus kredensial ini?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm rounded-circle px-2" title="Hapus Kredensial">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="bi bi-key fs-1 d-block mb-2 text-muted"></i>
                                                Belum ada data kredensial akun. Silakan masukkan data di sebelah kiri.
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
