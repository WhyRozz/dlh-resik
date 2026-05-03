<script>
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;
        const mainContent = document.getElementById('mainContent');
        const menuToggle = document.getElementById('menuToggle');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutBtnMobile = document.getElementById('logoutBtnMobile');
        const popup = document.getElementById('popupLogout');

        // Toggle mobile sidebar
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                mobileSidebar.style.display = mobileSidebar.style.display === 'block' ? 'none' : 'block';
            });
        }

        // Close sidebar on outside click
        document.addEventListener('click', function(e) {
            if (menuToggle && !menuToggle.contains(e.target) && mobileSidebar && !mobileSidebar.contains(e.target)) {
                mobileSidebar.style.display = 'none';
            }
        });

        // Show logout popup
        const showLogout = () => { if(popup) popup.style.display = 'flex'; };
        if (logoutBtn) logoutBtn.addEventListener('click', showLogout);
        if (logoutBtnMobile) logoutBtnMobile.addEventListener('click', showLogout);

        // Close popup on outside click
        document.addEventListener('click', function(e) {
            if (popup && !popup.contains(e.target) &&
                (!logoutBtn || !logoutBtn.contains(e.target)) &&
                (!logoutBtnMobile || !logoutBtnMobile.contains(e.target))) {
                popup.style.display = 'none';
            }
        });

        // Fade-in animation
        setTimeout(() => body.classList.add('fade-in-ready'), 50);

        // Fade-out on navigation
        document.querySelectorAll('.menu-item a, .filter-controls a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if(mainContent) mainContent.style.opacity = '0';
                setTimeout(() => window.location.href = this.href, 200);
            });
        });

        // Fade-out on form submit
        document.querySelectorAll('.filter-controls form').forEach(form => {
            form.addEventListener('submit', () => {
                if(mainContent) mainContent.style.opacity = '0';
            });
        });
    });

    function logout() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('admin.logout') }}";
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function closePopup() {
        const popup = document.getElementById('popupLogout');
        if(popup) popup.style.display = 'none';
    }
</script>
