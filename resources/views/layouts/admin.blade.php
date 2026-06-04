<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key', '') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'ap1') }}"

    <title>@yield('title', 'Admin RESIK')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-hamburger.css') }}">
    
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

    @include('admin.partials.mobile-navbar')
        <div class="admin-wrapper">
        <main>
            @yield('content')
        </main>
    </div>
    
    <!-- Sidebar JS -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="{{ asset('js/mobile-hamburger.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>



    <script>
// Pastikan fungsi closeNotifModal tersedia
function closeNotifModal() {
    const modal = document.getElementById('notifModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
}

// Close modal saat klik di luar area modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('notifModal');
    
    if (modal) {
        // Close saat klik di overlay (luar modal)
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeNotifModal();
            }
        });
        
        // Close dengan tombol ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display !== 'none') {
                closeNotifModal();
            }
        });
    }
});
</script>

    @stack('scripts')
</body>

</html>