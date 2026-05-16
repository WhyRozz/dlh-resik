
function toggleMenu() {
    const menu = document.querySelector('.navbar-menu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

// ===== WASTE TYPE TABS =====

/**
 * FUNGSI: Switch tab konten jenis sampah (organik / non-organik)
 * PARAM: {string} type - 'organik' atau 'non-organik'
 * INTERAKSI: Dipanggil via onclick pada tombol tab
 */
function showWasteType(type) {
    // Hide all content
    document.querySelectorAll('.waste-content').forEach(content => {
        content.classList.remove('active');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.waste-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected content
    const selectedContent = document.getElementById(type);
    if (selectedContent) {
        selectedContent.classList.add('active');
    }

    // Add active class to clicked tab
    if (event?.target) {
        const clickedTab = event.target.closest('.waste-tab');
        if (clickedTab) {
            clickedTab.classList.add('active');
        }
    }
}

// ===== SMOOTH SCROLL =====

/**
 * FUNGSI: Setup smooth scroll untuk anchor links
 * INTERAKSI: Auto-trigger saat DOM ready
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ===== SCROLL ANIMATIONS (Opsional) =====

/**
 * FUNGSI: Tambahkan animasi slide-in saat elemen masuk viewport
 * INTERAKSI: Auto-trigger via Intersection Observer
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'slideInLeft 0.6s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Animasi untuk feature cards dan waste items
    document.querySelectorAll('.feature-card, .waste-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateX(-30px)';
        observer.observe(el);
    });
}

// ===== GLOBAL INIT =====

/**
 * FUNGSI: Setup semua event listeners saat DOM ready
 * INTERAKSI: Auto-trigger saat halaman selesai load
 */
document.addEventListener('DOMContentLoaded', function() {
    initSmoothScroll();
    initScrollAnimations();
});

// ===== EXPORT FUNCTIONS (Opsional) =====
window.LandingJS = {
    toggleMenu,
    showWasteType
};