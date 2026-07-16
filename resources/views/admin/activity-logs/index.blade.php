@extends('layouts.app')

@section('title', 'Log Aktivitas Admin')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Log Aktivitas</h3>
                <p class="text-subtitle text-muted">Audit trail aksi yang dilakukan oleh seluruh administrator.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Log Aktivitas</li>
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
                <h4 class="card-title mb-3">Cari Log</h4>
                <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari aksi, deskripsi, atau nama admin..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Cari</button>
                    </div>
                </form>
            </div>
            <div class="card-body mt-4">
                <div class="table-responsive">
                    <table class="table table-striped table-lg">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Aksi / Kategori</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>
                                        @if ($log->admin)
                                            <span class="fw-bold">{{ $log->admin->name }}</span><br>
                                            <span class="small text-muted">{{ $log->admin->email }}</span>
                                        @else
                                            <span class="text-muted italic">System / Bot</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light-secondary text-dark">
                                            {{ strtoupper($log->action) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td><code>{{ $log->ip_address ?: '-' }}</code></td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Log aktivitas kosong.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
                    </div>
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
