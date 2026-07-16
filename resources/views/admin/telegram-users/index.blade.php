@extends('layouts.app')

@section('title', 'Daftar User Telegram')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>User Telegram</h3>
                <p class="text-subtitle text-muted">Daftar pengguna Telegram yang berinteraksi dengan Bot.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Telegram</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="card-title mb-3">Cari User</h4>
                <form method="GET" action="{{ route('admin.telegram-users.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, username, atau ID..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Diblokir</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                    </div>
                </form>
            </div>
            <div class="card-body mt-4">
                <div class="table-responsive">
                    <table class="table table-hover table-lg">
                        <thead>
                            <tr>
                                <th>Telegram ID</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Jumlah Transaksi</th>
                                <th>Status Bot</th>
                                <th>Terdaftar Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td><code>{{ $user->telegram_id }}</code></td>
                                    <td>@`{{ $user->username ?: 'no_username' }}`</td>
                                    <td class="fw-bold">{{ $user->full_name }}</td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">{{ $user->orders_count }} order</span>
                                    </td>
                                    <td>
                                        @if ($user->is_blocked)
                                            <span class="badge bg-danger">Diblokir</span>
                                        @else
                                            <span class="badge bg-success">Aktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.telegram-users.show', $user) }}" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye-fill me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Tidak ditemukan user Telegram.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
