@extends('layouts.app')

@section('title', 'Edit Digital File')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Digital File</h3>
                <p class="text-subtitle text-muted">Perbarui data konten pengiriman digital otomatis.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.digital-files.index') }}">Digital Files</a></li>
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
                <h4 class="card-title">Form Edit Digital File</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.digital-files.update', $digitalFile) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label for="product_variant_id" class="form-label fw-bold">Pilih Varian Produk <span class="text-danger">*</span></label>
                            <select id="product_variant_id" name="product_variant_id" class="form-select @error('product_variant_id') is-invalid @enderror" required>
                                @foreach ($variants as $var)
                                    <option value="{{ $var->id }}" {{ old('product_variant_id', $digitalFile->product_variant_id) == $var->id ? 'selected' : '' }}>
                                        [{{ $var->product->name }}] {{ $var->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_variant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="delivery_type" class="form-label fw-bold">Tipe Pengiriman <span class="text-danger">*</span></label>
                            <select id="delivery_type" name="delivery_type" class="form-select @error('delivery_type') is-invalid @enderror" onchange="toggleFormFields()" required>
                                @foreach ($deliveryTypes as $type)
                                    <option value="{{ $type->value }}" {{ old('delivery_type', $digitalFile->delivery_type->value) == $type->value ? 'selected' : '' }}>
                                        {{ strtoupper($type->value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('delivery_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- TEXT FIELD -->
                        <div class="col-12 mb-3 d-none" id="text-field-container">
                            <label for="content" class="form-label fw-bold">Konten Teks / Lisensi <span class="text-danger">*</span></label>
                            <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror"
                                      rows="5">{{ old('content', $digitalFile->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- FILE FIELD -->
                        <div class="col-md-6 col-12 mb-3 d-none" id="file-field-container">
                            <label for="file" class="form-label fw-bold">Upload File Digital</label>
                            <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror">
                            @if ($digitalFile->delivery_type->value === 'file' && $digitalFile->file_path)
                                <div class="form-text text-muted small mt-1">
                                    <i class="bi bi-file-earmark-check"></i> File saat ini: <strong>{{ $digitalFile->file_name }}</strong>
                                </div>
                            @endif
                            <div class="form-text small">Kosongkan jika tidak ingin mengubah file. Maksimal ukuran file: 50MB.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- MANUAL FIELD -->
                        <div class="col-12 mb-3 d-none" id="manual-field-container">
                            <label for="notes" class="form-label fw-bold">Petunjuk Pengiriman Manual <span class="text-danger">*</span></label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="3">{{ old('notes', $digitalFile->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Perbarui</button>
                        <a href="{{ route('admin.digital-files.index') }}" class="btn btn-light-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    function toggleFormFields() {
        const type = document.getElementById('delivery_type').value;
        const textField = document.getElementById('text-field-container');
        const fileField = document.getElementById('file-field-container');
        const manualField = document.getElementById('manual-field-container');

        // Reset
        textField.classList.add('d-none');
        fileField.classList.add('d-none');
        manualField.classList.add('d-none');

        if (type === 'text') {
            textField.classList.remove('d-none');
        } else if (type === 'file') {
            fileField.classList.remove('d-none');
        } else if (type === 'manual') {
            manualField.classList.remove('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleFormFields();
    });
</script>
@endsection
