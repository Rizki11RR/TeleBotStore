@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Kategori</h3>
                <p class="text-subtitle text-muted">Perbarui data kategori produk digital.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Kategori</a></li>
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
                <h4 class="card-title">Form Edit Kategori</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label for="name" class="form-label fw-bold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="icon" class="form-label fw-bold">Emoji / Icon Kategori</label>
                            <input type="text" id="icon" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                   placeholder="Contoh: 🚀 atau 📁" value="{{ old('icon', $category->icon) }}">
                            <div class="form-text small">Gunakan emoji sebagai ikon kategori pada Telegram Bot.</div>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="sort_order" class="form-label fw-bold">Urutan Tampil</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $category->sort_order) }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label fw-bold d-block">Status Kategori</label>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="is_active" id="active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Aktif</label>
                            </div>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0" {{ !$category->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="inactive">Tidak Aktif</label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-bold">Deskripsi</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Perbarui</button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
