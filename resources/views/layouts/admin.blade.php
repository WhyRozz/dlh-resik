<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key', '') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'ap1') }}">

    <title>@yield('title', 'Admin RESIK')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    @stack('styles')
</head>

<body>
    <!-- Modal Notifikasi -->
    <div id="notifModal" class="modal-overlay">
        <div class="notif-modal-card">
            <div class="notif-modal-header">
                <h3 id="modalTitle">Notifikasi</h3>
                <button class="notif-close-btn" onclick="closeNotifModal()">&times;</button>
            </div>
            <div class="notif-modal-body" id="modalBody">
                <div class="loading-spinner">Memuat data...</div>
            </div>
            <div class="notif-modal-footer">
                <a href="#" id="modalSeeAll" class="btn-see-all">Lihat Semua Data →</a>
            </div>
        </div>
    </div>
    @include('admin.partials.sidebar')

    <div class="admin-wrapper" style="margin-left: 260px; padding: 20px;">
        @include('admin.partials.navbar')
        <main>
            @yield('content')
        </main>
    </div>
    
    <!-- Sidebar JS -->
    <script src="{{ asset('js/sidebar.js') }}"></script>

    <!-- Script untuk disable notifikasi di Penjemputan -->
    @if(request()->routeIs('admin.bank-sampah.penjemputan*'))
    <script>
        // Disable notifikasi untuk halaman Penjemputan
        window.disableNotifPenjemputan = true;
        
        // Override fungsi openNotifModal
        window.openNotifModal = function(type) {
            if (type === 'penjemputan') {
                console.log('Notifikasi Penjemputan disabled');
                return;
            }
            // Untuk tipe lain, biarkan normal
        };
        
        // Override fungsi updateNotifBadges
        const originalUpdateBadges = window.updateNotifBadges;
        window.updateNotifBadges = function() {
            if (originalUpdateBadges) {
                // Panggil fungsi asli tapi skip badge penjemputan
                const originalGetElementById = document.getElementById.bind(document);
                document.getElementById = function(id) {
                    if (id === 'badge-penjemputan') {
                        return null; // Pura-pura badge tidak ada
                    }
                    return originalGetElementById(id);
                };
                originalUpdateBadges();
            }
        };
    </script>
    @endif

    <!-- Load notifications.js (compiled via Vite) -->
    @vite(['resources/js/notifications.js', 'resources/js/echo.js'])

    @stack('scripts')
</body>

</html>