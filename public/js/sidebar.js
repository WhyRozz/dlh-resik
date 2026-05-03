/**
 * SIDEBAR CONTROLLER - RESIK ADMIN
 * Vanilla JS, no dependencies
 */
document.addEventListener('DOMContentLoaded', () => {
    // Toggle Mobile Sidebar
    window.toggleSidebar = function () {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');

        // Prevent body scroll when sidebar is open on mobile
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    };

    // Toggle Dropdown Menu
    window.toggleDropdown = function (el) {
        const parent = el.closest('.has-dropdown');
        const isOpen = parent.classList.contains('open');

        // Close other dropdowns (optional, remove if you want multiple open)
        document.querySelectorAll('.has-dropdown.open').forEach(item => {
            if (item !== parent) item.classList.remove('open');
        });

        parent.classList.toggle('open');
        el.setAttribute('aria-expanded', !isOpen);
    };

    // Close sidebar on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('active')) toggleSidebar();
        }
    });
});
