@extends('layouts.app')

@section('title', 'Pengaturan Toko & Bot')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Pengaturan Sistem</h3>
                <p class="text-subtitle text-muted">Konfigurasi nama toko, info admin, dan template pesan default bot.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Pengaturan Umum Toko</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="store_name" class="form-label fw-bold">Nama Toko <span class="text-danger">*</span></label>
                                <input type="text" id="store_name" name="store_name" class="form-control"
                                       value="{{ old('store_name', \App\Models\Setting::get('store_name', 'Nexora Digital')) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="store_description" class="form-label fw-bold">Deskripsi Singkat Toko</label>
                                <input type="text" id="store_description" name="store_description" class="form-control"
                                       value="{{ old('store_description', \App\Models\Setting::get('store_description', 'Toko Produk Digital Terpercaya')) }}">
                            </div>

                            <div class="mb-3">
                                <label for="admin_telegram_id" class="form-label fw-bold">Telegram ID Admin (Untuk Notifikasi)</label>
                                <input type="text" id="admin_telegram_id" name="admin_telegram_id" class="form-control"
                                       value="{{ old('admin_telegram_id', \App\Models\Setting::get('admin_telegram_id')) }}" placeholder="Contoh: 12345678">
                                <div class="form-text small">Masukkan ID Telegram Anda untuk menerima notifikasi order masuk & unggahan bukti transfer.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Template Pesan Telegram Bot</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="bot_welcome_message" class="form-label fw-bold">Template Pesan Sambutan (/start) <span class="text-danger">*</span></label>
                                <textarea id="bot_welcome_message" name="bot_welcome_message" class="form-control" rows="3" required>{{ old('bot_welcome_message', \App\Models\Setting::get('bot_welcome_message', 'Halo! Selamat datang di Nexora Digital Store. Silakan pilih menu di bawah.')) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="bot_help_message" class="form-label fw-bold">Template Pesan Bantuan <span class="text-danger">*</span></label>
                                <textarea id="bot_help_message" name="bot_help_message" class="form-control" rows="3" required>{{ old('bot_help_message', \App\Models\Setting::get('bot_help_message', 'Butuh bantuan? Silakan gunakan menu Hubungi Admin.')) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="bot_payment_info" class="form-label fw-bold">Template Pesan Info Pembayaran <span class="text-danger">*</span></label>
                                <textarea id="bot_payment_info" name="bot_payment_info" class="form-control" rows="3" required>{{ old('bot_payment_info', \App\Models\Setting::get('bot_payment_info', 'Pembayaran menggunakan QRIS. Silakan scan QRIS di atas lalu kirim foto bukti transfer.')) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="bot_contact_admin" class="form-label fw-bold">Template Pesan Hubungi Admin <span class="text-danger">*</span></label>
                                <textarea id="bot_contact_admin" name="bot_contact_admin" class="form-control" rows="3" required>{{ old('bot_contact_admin', \App\Models\Setting::get('bot_contact_admin', 'Hubungi Telegram kami di @nexora_admin jika ada kendala.')) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-5">Simpan Semua Pengaturan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
