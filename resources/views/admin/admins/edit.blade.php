@extends('layouts.app')

@section('title', 'Edit Akun Administrator')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Administrator</h3>
                <p class="text-subtitle text-muted">Perbarui data profil administrator.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card col-md-8 col-12">
            <div class="card-header">
                <h4 class="card-title">Form Edit Admin</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.admins.update', $admin) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $admin->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $admin->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label for="password" class="form-label fw-bold">Password Baru</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Kosongkan jika tidak ingin diubah">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">Konfirmasi Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                                   placeholder="Kosongkan jika tidak ingin diubah">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Perbarui</button>
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-light-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
