<script>
    // Fungsi: Menjalankan kode setelah DOM sepenuhnya loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi: Deklarasi variabel elemen-elemen DOM yang akan digunakan
        const body = document.body;
        const mainContent = document.getElementById('mainContent');
        const menuToggle = document.getElementById('menuToggle');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutBtnMobile = document.getElementById('logoutBtnMobile');
        const popup = document.getElementById('popupLogout');

        // Fungsi: Toggle menampilkan/menyembunyikan sidebar mobile saat tombol menu diklik
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                mobileSidebar.style.display = mobileSidebar.style.display === 'block' ? 'none' : 'block';
            });
        }

        // Fungsi: Menutup sidebar mobile ketika user klik di luar area sidebar
        document.addEventListener('click', function(e) {
            if (menuToggle && !menuToggle.contains(e.target) && mobileSidebar && !mobileSidebar.contains(e.target)) {
                mobileSidebar.style.display = 'none';
            }
        });

        // Fungsi: Helper untuk menampilkan popup konfirmasi logout
        const showLogout = () => { if(popup) popup.style.display = 'flex'; };
        // Fungsi: Event listener untuk tombol logout di desktop
        if (logoutBtn) logoutBtn.addEventListener('click', showLogout);
        // Fungsi: Event listener untuk tombol logout di mobile
        if (logoutBtnMobile) logoutBtnMobile.addEventListener('click', showLogout);

        // Fungsi: Menutup popup logout ketika user klik di luar area popup
        document.addEventListener('click', function(e) {
            if (popup && !popup.contains(e.target) &&
                (!logoutBtn || !logoutBtn.contains(e.target)) &&
                (!logoutBtnMobile || !logoutBtnMobile.contains(e.target))) {
                popup.style.display = 'none';
            }
        });

        // Fungsi: Trigger animasi fade-in setelah halaman selesai load
        setTimeout(() => body.classList.add('fade-in-ready'), 50);

        // Fungsi: Animasi fade-out saat user klik menu navigasi untuk transisi halus
        document.querySelectorAll('.menu-item a, .filter-controls a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if(mainContent) mainContent.style.opacity = '0';
                setTimeout(() => window.location.href = this.href, 200);
            });
        });

        // Fungsi: Animasi fade-out saat user submit form filter
        document.querySelectorAll('.filter-controls form').forEach(form => {
            form.addEventListener('submit', () => {
                if(mainContent) mainContent.style.opacity = '0';
            });
        });
    });

    // Fungsi: Proses logout dengan membuat form POST dinamis ke route logout Laravel
    function logout() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.logout') }}";
        // Fungsi: Menambahkan CSRF token untuk keamanan request POST
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    // Fungsi: Menutup popup konfirmasi logout
    function closePopup() {
        const popup = document.getElementById('popupLogout');
        if(popup) popup.style.display = 'none';
    }
</script>