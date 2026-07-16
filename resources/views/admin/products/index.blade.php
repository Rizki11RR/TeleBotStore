@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Kelola Produk</h3>
                <p class="text-subtitle text-muted">Daftar produk digital Nexora Digital.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Produk</li>
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
                <h4 class="card-title">Daftar Produk</h4>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-lg">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Kategori</th>
                                <th>Nama Produk</th>
                                <th>Tipe</th>
                                <th>Jumlah Varian</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-products">
                            @forelse ($products as $prod)
                                <tr data-id="{{ $prod->id }}">
                                    <td>
                                        <i class="bi bi-grip-vertical text-muted drag-handle fs-5" style="cursor: move;"></i>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-secondary text-dark">
                                            {{ $prod->category->icon ?: '📁' }} {{ $prod->category->name }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">
                                        <a href="{{ route('admin.products.show', $prod) }}" class="text-primary">{{ $prod->name }}</a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-info text-info">
                                            {{ $prod->type === 'account' ? 'Akun' : 'Ebook' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">
                                            {{ $prod->variants_count }} varian
                                        </span>
                                    </td>
                                    <td>
                                        @if ($prod->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.products.show', $prod) }}" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="{{ route('admin.products.edit', $prod) }}" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $prod) }}" method="POST" class="delete-form"
                                                  data-confirm="Apakah Anda yakin ingin menghapus produk ini? Semua varian di dalamnya akan terpengaruh.">
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada data produk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} entries
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('sortable-products');
        if (el) {
            const sortable = new Sortable(el, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function () {
                    const ids = Array.from(el.querySelectorAll('tr')).map(tr => tr.getAttribute('data-id'));
                    
                    fetch("{{ route('admin.products.reorder') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                        } else {
                            showToast('Gagal memperbarui urutan.', 'danger');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('Terjadi kesalahan sistem.', 'danger');
                    });
                }
            });
        }

        function showToast(message, type) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.position = 'fixed';
                container.style.bottom = '20px';
                container.style.right = '20px';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible show fade shadow-lg`;
            toast.style.minWidth = '250px';
            toast.style.marginBottom = '10px';
            toast.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold">${message}</span>
                    <button type="button" class="btn-close ms-2" style="position:static; padding:0; background:none; border:none; color:inherit; font-size:1.25rem;" onclick="this.parentElement.parentElement.remove()">×</button>
                </div>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    });
</script>
@endpush
@endsection
