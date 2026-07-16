@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Produk</h3>
                <p class="text-subtitle text-muted">Perbarui data produk digital.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produk</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
                <h4 class="card-title">Form Edit Produk</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label for="category_id" class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->icon ?: '📁' }} {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="name" class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="sort_order" class="form-label fw-bold">Urutan Tampil</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $product->sort_order) }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label fw-bold d-block">Status Produk</label>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="is_active" id="active" value="1" {{ $product->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Aktif</label>
                            </div>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0" {{ !$product->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="inactive">Tidak Aktif</label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-bold">Deskripsi Produk</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="4">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Perbarui</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
