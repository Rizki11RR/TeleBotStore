@extends('layouts.app')

@section('title', 'Varian Produk')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Varian Produk</h3>
                <p class="text-subtitle text-muted">Kelola semua varian item produk digital.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Varian</li>
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
                <h4 class="card-title">Daftar Varian Produk</h4>
                <a href="{{ route('admin.variants.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Varian
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-lg">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Nama Varian</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($variants as $var)
                                <tr>
                                    <td>
                                        <span class="text-muted small">[{{ $var->product->category->name }}]</span><br>
                                        <strong>{{ $var->product->name }}</strong>
                                    </td>
                                    <td class="fw-bold text-primary">{{ $var->name }}</td>
                                    <td>Rp{{ number_format($var->price, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($var->stock == -1)
                                            <span class="badge bg-light-info text-info">Unlimited</span>
                                        @else
                                            {{ $var->stock }}
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
                                            <form action="{{ route('admin.variants.destroy', $var) }}" method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus varian ini?')">
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
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada data varian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $variants->firstItem() ?? 0 }} to {{ $variants->lastItem() ?? 0 }} of {{ $variants->total() }} entries
                    </div>
                    <div>
                        {{ $variants->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
