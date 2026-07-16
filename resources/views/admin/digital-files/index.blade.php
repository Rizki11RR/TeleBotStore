@extends('layouts.app')

@section('title', 'Digital Files')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Digital Files</h3>
                <p class="text-subtitle text-muted">Kelola konten pengiriman digital otomatis.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Digital Files</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Daftar Digital Files</h4>
                <a href="{{ route('admin.digital-files.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Digital File
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-lg">
                        <thead>
                            <tr>
                                <th>Produk & Varian</th>
                                <th>Tipe Pengiriman</th>
                                <th>Konten / File</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($digitalFiles as $df)
                                <tr>
                                    <td>
                                        <span class="text-muted small">[{{ $df->productVariant->product->category->name }}]</span><br>
                                        <strong>{{ $df->productVariant->product->name }}</strong> - <span class="text-primary">{{ $df->productVariant->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">
                                            {{ strtoupper($df->delivery_type->value) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($df->delivery_type->value === 'file')
                                            <span class="small"><i class="bi bi-file-earmark-arrow-down me-1"></i> {{ $df->file_name }}</span>
                                        @elseif ($df->delivery_type->value === 'text')
                                            <span class="small text-truncate d-inline-block" style="max-width: 300px;">{{ $df->content }}</span>
                                        @else
                                            <span class="small text-muted italic">Manual: {{ $df->notes ?: 'Tindakan manual admin' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.digital-files.edit', $df) }}" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <form action="{{ route('admin.digital-files.destroy', $df) }}" method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus file digital ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Belum ada data digital file.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $digitalFiles->firstItem() ?? 0 }} to {{ $digitalFiles->lastItem() ?? 0 }} of {{ $digitalFiles->total() }} entries
                    </div>
                    <div>
                        {{ $digitalFiles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
