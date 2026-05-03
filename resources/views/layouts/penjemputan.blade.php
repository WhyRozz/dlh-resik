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

        {{-- Navbar --}}
        <nav class="admin-navbar">
            <button class="menu-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>

            <div class="navbar-search">
                <input type="text" placeholder="Cari data berdasarkan nama atau lokasi...">
                <button><i class="fas fa-search"></i></button>
            </div>

            <div class="navbar-user">
                <span>Admin RESIK</span>
                <div class="avatar">A</div>
            </div>
        </nav>

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