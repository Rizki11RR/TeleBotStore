@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Detail Produk: {{ $product->name }}</h3>
                <p class="text-subtitle text-muted">Informasi produk dan varian yang terhubung.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produk</a></li>
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
                        <h4 class="card-title">Informasi Produk</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Kategori</span>
                                <span class="badge bg-light-primary text-primary">{{ $product->category->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Urutan Tampil</span>
                                <strong>{{ $product->sort_order }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Status</span>
                                @if ($product->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </li>
                            <li class="list-group-item px-0">
                                <span class="d-block mb-1 text-muted">Deskripsi</span>
                                <p class="mb-0 small text-justify">{{ $product->description ?: 'Tidak ada deskripsi.' }}</p>
                            </li>
                        </ul>
                        <div class="d-flex gap-2 mt-3 justify-content-end">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-fill me-1"></i> Edit
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Daftar Varian Produk</h4>
                        <a href="{{ route('admin.variants.create', ['product_id' => $product->id]) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Varian
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Varian</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Digital File / Tipe</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($product->variants as $var)
                                        <tr>
                                            <td class="fw-bold">{{ $var->name }}</td>
                                            <td>Rp{{ number_format($var->price, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($var->stock == -1)
                                                    <span class="badge bg-light-info text-info">Unlimited</span>
                                                @else
                                                    {{ $var->stock }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($var->digitalFile)
                                                    <span class="badge bg-light-success text-success">
                                                        {{ strtoupper($var->digitalFile->delivery_type->value) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-warning text-warning">Belum Ada File</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($var->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.variants.edit', $var) }}" class="btn btn-warning btn-sm">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                Belum ada varian produk.
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
