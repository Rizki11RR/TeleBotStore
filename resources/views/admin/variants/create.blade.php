@extends('layouts.app')

@section('title', 'Tambah Varian')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Tambah Varian Produk</h3>
                <p class="text-subtitle text-muted">Buat varian baru untuk produk digital.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.variants.index') }}">Varian</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Tambah Varian</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.variants.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label for="product_id" class="form-label fw-bold">Pilih Produk <span class="text-danger">*</span></label>
                            <select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Produk --</option>
                                @foreach ($products as $prod)
                                    <option value="{{ $prod->id }}" {{ (old('product_id') ?: request('product_id')) == $prod->id ? 'selected' : '' }}>
                                        [{{ $prod->category->name }}] {{ $prod->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="name" class="form-label fw-bold">Nama Varian <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Contoh: Paket 1 Bulan / 1000 Followers" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="price" class="form-label fw-bold">Harga Rupiah (IDR) <span class="text-danger">*</span></label>
                            <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                                   placeholder="Contoh: 50000" value="{{ old('price') }}" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="stock" class="form-label fw-bold">Jumlah Stok <span class="text-danger">*</span></label>
                            <input type="number" id="stock" name="stock" class="form-control @error('stock') is-invalid @enderror"
                                   value="{{ old('stock', -1) }}" min="-1" required>
                            <div class="form-text small">Isi `-1` untuk stok tak terbatas (unlimited).</div>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label fw-bold d-block">Status Varian</label>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="is_active" id="active" value="1" checked>
                                <label class="form-check-label" for="active">Aktif</label>
                            </div>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0">
                                <label class="form-check-label" for="inactive">Tidak Aktif</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                        <a href="{{ route('admin.variants.index') }}" class="btn btn-light-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
