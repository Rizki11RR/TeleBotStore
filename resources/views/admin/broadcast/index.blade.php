@extends('layouts.app')

@section('title', 'Kirim Broadcast Telegram')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Broadcast Pesan</h3>
                <p class="text-subtitle text-muted">Kirim pesan massal ke seluruh pengguna Telegram Bot.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Broadcast</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="row">
            <div class="col-md-7 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Form Broadcast Telegram</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.broadcast.send') }}" method="POST" class="confirm-form"
                              data-confirm="Apakah Anda yakin ingin mengirimkan pesan ini ke target terpilih?"
                              data-confirm-title="Kirim Broadcast Telegram"
                              data-confirm-button="Ya, Kirim!"
                              data-confirm-color="#435ebe"
                              data-confirm-icon="question">
                            @csrf
                            <div class="mb-3">
                                <label for="target" class="form-label fw-bold">Target Penerima <span class="text-danger">*</span></label>
                                <select id="target" name="target" class="form-select @error('target') is-invalid @enderror" required>
                                    <option value="all">Semua User ({{ $allUsersCount }} user)</option>
                                    <option value="active">User Aktif / Pernah Belanja ({{ $activeUsersCount }} user)</option>
                                </select>
                                @error('target')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label fw-bold">Isi Pesan <span class="text-danger">*</span></label>
                                <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror"
                                          rows="8" placeholder="Tulis pesan di sini... (Mendukung format Markdown)" required>{{ old('message') }}</textarea>
                                <div class="form-text small">
                                    Format Markdown yang didukung:<br>
                                    `*Teks Tebal*` → <strong>Teks Tebal</strong><br>
                                    `_Teks Miring_` → <em>Teks Miring</em><br>
                                    ``Teks Code`` → <code>Teks Code</code>
                                </div>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-telegram me-1"></i> Kirim Broadcast
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Informasi & Panduan</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="bi bi-info-circle-fill"></i> Menggunakan Queue</h5>
                            <p class="small mb-0">Pesan broadcast tidak dikirim langsung secara bersamaan untuk mencegah limitasi API Telegram (Rate Limit). Pesan dimasukkan ke antrean database (Queue) dan dikirim di latar belakang secara otomatis.</p>
                        </div>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Penerima Aktif (Bot Terhubung)</span>
                                <span class="badge bg-success">{{ $allUsersCount }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Pernah Melakukan Order</span>
                                <span class="badge bg-primary">{{ $activeUsersCount }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
