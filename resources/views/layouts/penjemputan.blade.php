<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin RESIK')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/penjemputan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
</head>
<body>

    {{-- Sidebar --}}
    @include('admin.partials.sidebar')

    {{-- Wrapper kanan (navbar + konten) --}}
    <div class="main-wrapper" id="mainWrapper">


        {{-- Konten Halaman --}}
        <main class="main-content">
            @yield('content')
        </main>

    </div>

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
</body>
</html>