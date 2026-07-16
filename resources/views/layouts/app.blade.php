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

    {{-- Page-Specific JS --}}
    @stack('scripts')
</body>

</html>
