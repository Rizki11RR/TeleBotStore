<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Nexora Digital') }}</title>

    {{-- Mazer Core CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">

    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('assets/static/images/logo/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/static/images/logo/favicon.png') }}" type="image/png">

    {{-- SweetAlert2 & Toastify --}}
    <link rel="stylesheet" href="{{ asset('assets/extensions/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/toastify-js/src/toastify.css') }}">
    <script src="{{ asset('assets/extensions/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/extensions/toastify-js/src/toastify.js') }}"></script>

    {{-- Page-Specific CSS --}}
    @stack('styles')
</head>

<body>
    {{-- Theme init (mencegah flash putih saat dark mode) --}}
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>

    <div id="app">
        {{-- Sidebar --}}
        <div id="sidebar">
            @include('partials.sidebar')
        </div>

        {{-- Main Content --}}
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            {{-- Flash Messages --}}
            @include('partials.alerts')

            {{-- Page Content --}}
            @yield('content')

            {{-- Footer --}}
            @include('partials.footer')
        </div>
    </div>

    {{-- Mazer Core JS --}}
    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>

    {{-- Global SweetAlert2 & Toastify Config --}}
    <script>
        document.addEventListener('submit', function (e) {
            if (e.target && (e.target.classList.contains('delete-form') || e.target.classList.contains('confirm-form'))) {
                e.preventDefault();
                const form = e.target;
                const isDelete = form.classList.contains('delete-form');
                
                const confirmMessage = form.getAttribute('data-confirm') || (isDelete ? 'Apakah Anda yakin ingin menghapus data ini?' : 'Apakah Anda yakin ingin melanjutkan?');
                const confirmTitle = form.getAttribute('data-confirm-title') || (isDelete ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan');
                const confirmButtonText = form.getAttribute('data-confirm-button') || (isDelete ? 'Ya, Hapus!' : 'Ya, Lanjutkan');
                const cancelButtonText = form.getAttribute('data-cancel-button') || 'Batal';
                const confirmColor = form.getAttribute('data-confirm-color') || (isDelete ? '#dc3545' : '#435ebe');
                const confirmIcon = form.getAttribute('data-confirm-icon') || (isDelete ? 'warning' : 'question');
                
                Swal.fire({
                    title: confirmTitle,
                    text: confirmMessage,
                    icon: confirmIcon,
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: cancelButtonText,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>

    {{-- Page-Specific JS --}}
    @stack('scripts')
</body>

</html>
