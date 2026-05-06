<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin RESIK')</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- CSS: Sidebar dulu, baru Penjemputan --}}
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penjemputan.css') }}">
    
    {{-- ✅ INI RAHASIANYA: Paksa Font & Smoothing di Level Layout --}}
    <style>
        html, body {
            /* Menggunakan System Font Stack biar sama persis kayak layout.admin */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
            /* Bikin font tajam dan halus */
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: optimizeLegibility !important;
        }
        
        /* Pastikan semua elemen turunannya ikut */
        *, *::before, *::after {
            font-family: inherit !important;
        }

        /* Fix Sidebar Font Weight biar nggak tebal aneh */
        .sidebar .nav-item a, 
        .sidebar .sub-menu a {
            font-weight: 500 !important;
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Sidebar --}}
    @include('admin.partials.sidebar')

    {{-- Wrapper kanan --}}
    <div class="main-wrapper" id="mainWrapper">
        {{-- Konten Halaman --}}
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    {{-- Script Toggle Sidebar --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        function toggleDropdown(el) {
            const parent = el.closest('.nav-item');
            parent.classList.toggle('open');
        }
    </script>
    
    @stack('scripts')
</body>
</html>