@extends('layouts.auth')

@section('title', 'Login Admin')

@section('content')
<div class="row h-100">
    <div class="col-lg-5 col-12">
        <div id="auth-left">
            {{-- Logo / Brand --}}
            <div class="auth-logo mb-4">
                <h2 class="fw-bold text-primary">⚡ Nexora Digital</h2>
                <p class="text-muted small">Admin Panel</p>
            </div>

            <h1 class="auth-title">Masuk.</h1>
            <p class="auth-subtitle mb-5">Masuk dengan akun admin Anda.</p>

            {{-- Flash error --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Login Form --}}
            <form action="{{ route('admin.login') }}" method="POST" id="login-form">
                @csrf

                <div class="form-group position-relative has-icon-left mb-4">
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control form-control-xl @error('email') is-invalid @enderror"
                        placeholder="Email Admin"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                    <div class="form-control-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group position-relative has-icon-left mb-4">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control form-control-xl @error('password') is-invalid @enderror"
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                    >
                    <div class="form-control-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-check-lg d-flex align-items-end mb-4">
                    <input class="form-check-input me-2" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label text-gray-600" for="remember">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-2" id="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Masuk
                </button>
            </form>

            <div class="text-center mt-5 text-muted small">
                <p>&copy; {{ date('Y') }} Nexora Digital. All rights reserved.</p>
            </div>
        </div>
    </div>

    {{-- Right side decorative panel --}}
    <div class="col-lg-7 d-none d-lg-block">
        <div id="auth-right" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);">
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white p-5">
                <h1 class="display-4 fw-bold mb-3">⚡</h1>
                <h2 class="fw-bold mb-3">Nexora Digital</h2>
                <p class="lead text-center opacity-75">
                    Sistem penjualan produk digital terintegrasi<br>
                    dengan Telegram Bot
                </p>
                <div class="mt-5 d-flex gap-4 text-center">
                    <div>
                        <h4 class="fw-bold mb-0">Bot</h4>
                        <small class="opacity-75">Telegram</small>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">QRIS</h4>
                        <small class="opacity-75">Pembayaran</small>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Auto</h4>
                        <small class="opacity-75">Pengiriman</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
