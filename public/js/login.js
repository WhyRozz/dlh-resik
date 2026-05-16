// ===== STATE =====
let loginConfig = window.LoginConfig || {};

// ===== POPUP FUNCTIONS =====

/**
 * FUNGSI: Tampilkan popup notifikasi dengan judul dan pesan
 * PARAM: {string} title - Judul popup
 * PARAM: {string} message - Pesan yang akan ditampilkan
 * INTERAKSI: Dipanggil saat ada error login dari server
 */
function showPopup(title, message) {
    document.getElementById('popup-title').textContent = title;
    document.getElementById('popup-message').textContent = message;
    document.getElementById('popup').classList.add('show');
}

/**
 * FUNGSI: Tutup popup notifikasi
 * INTERAKSI: Dipanggil via onclick pada tombol tutup / klik di luar popup
 */
function closePopup() {
    document.getElementById('popup').classList.remove('show');
}

// ===== ALERT FUNCTIONS =====

/**
 * FUNGSI: Tampilkan alert inline di form dengan pesan
 * PARAM: {string} message - Pesan error yang akan ditampilkan
 * INTERAKSI: Dipanggil saat validasi client-side gagal
 */
function showAlert(message) {
    const alertBox = document.getElementById('alertBox');
    alertBox.textContent = message;
    alertBox.classList.add('show');
    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 5000);
}

// ===== PASSWORD TOGGLE =====

/**
 * FUNGSI: Toggle visibility password dan ganti icon eye
 * INTERAKSI: Dipanggil via onclick pada tombol toggle password
 * CATATAN: Icon paths dipassing via window.LoginConfig dari Blade
 */
function togglePasswordVisibility() {
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (!password || !eyeIcon) return;

    if (password.type === "password") {
        password.type = "text";
        eyeIcon.src = loginConfig.iconShow || '/assets/show1.png';
        eyeIcon.alt = "Sembunyikan sandi";
    } else {
        password.type = "password";
        eyeIcon.src = loginConfig.iconHide || '/assets/hide1.png';
        eyeIcon.alt = "Tampilkan sandi";
    }
}

// ===== FORM VALIDATION & SUBMIT =====

/**
 * FUNGSI: Validasi client-side form login sebelum submit
 * INTERAKSI: Dipanggil otomatis saat user submit form
 * CATATAN: Validasi server-side tetap wajib di Controller
 */
function initLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const email = document.getElementById('email')?.value.trim();
        const password = document.getElementById('password')?.value.trim();

        if (!email) {
            showAlert('Email wajib diisi.');
            return;
        }
        if (!password) {
            showAlert('Kata sandi wajib diisi.');
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showAlert('Format email tidak valid.');
            return;
        }

        // Jika validasi client-side lolos, submit form ke server
        this.submit();
    });
}

// ===== EVENT LISTENERS =====

/**
 * FUNGSI: Setup event listeners saat DOM ready
 * INTERAKSI: Auto-trigger saat halaman selesai load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Init form validation
    initLoginForm();

    // Close popup when clicking outside
    const popup = document.getElementById('popup');
    if (popup) {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup();
            }
        });
    }

    // Show error/success popup from bridge config
    if (loginConfig.popupTitle && loginConfig.popupMessage) {
        showPopup(loginConfig.popupTitle, loginConfig.popupMessage);
    }
});

// ===== EXPORT FUNCTIONS (Opsional) =====
window.LoginJS = {
    showPopup, closePopup, showAlert, togglePasswordVisibility
};