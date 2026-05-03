/**
 * Script untuk halaman Kelola Artikel Admin
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah ada pesan notifikasi dari URL params
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        showNotification(decodeURIComponent(urlParams.get('success')), 'success');
        // Bersihkan URL
        const newUrl = window.location.origin + window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }
});

// Variabel untuk menyimpan ID yang akan dihapus
let idYangAkanDihapus = null;

/**
 * Tampilkan popup konfirmasi hapus
 * @param {number} id - ID artikel
 */
function konfirmasiHapus(id) {
    idYangAkanDihapus = id;
    const popup = document.getElementById('confirmPopup');
    if (popup) {
        popup.classList.add('active');
        setTimeout(() => {
            popup.querySelector('.popup-content')?.classList.add('show');
        }, 10);
    }
}

/**
 * Tutup popup konfirmasi hapus
 */
function closeConfirmPopup() {
    const popup = document.getElementById('confirmPopup');
    if (popup) {
        popup.querySelector('.popup-content')?.classList.remove('show');
        setTimeout(() => {
            popup.classList.remove('active');
        }, 300);
    }
    idYangAkanDihapus = null;
}

/**
 * Hapus artikel (submit form)
 */
function hapusArtikel() {
    if (idYangAkanDihapus === null) {
        console.error('Tidak ada ID yang dipilih untuk dihapus.');
        closeConfirmPopup();
        return;
    }

    // Buat form dinamis untuk submit DELETE request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/artikel/${idYangAkanDihapus}`;

    // Tambahkan CSRF token dan method spoofing
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
        <input type="hidden" name="_method" value="DELETE">
    `;

    document.body.appendChild(form);
    form.submit();
}

/**
 * Tampilkan popup notifikasi
 * @param {string} message - Pesan notifikasi
 * @param {string} type - 'success' atau 'error'
 */
function showNotification(message, type = 'error') {
    const popup = document.getElementById('notificationPopup');
    if (!popup) return;

    const titleElement = document.getElementById('notificationTitle');
    const messageElement = document.getElementById('notificationMessage');
    const content = popup.querySelector('.popup-content');

    if (titleElement) titleElement.textContent = type === 'success' ? 'Berhasil!' : 'Gagal!';
    if (messageElement) messageElement.textContent = message;
    if (content) {
        content.className = `popup-content ${type}`;
    }

    popup.classList.add('active');
    setTimeout(() => {
        content?.classList.add('show');
    }, 10);
}

/**
 * Tutup popup notifikasi
 */
function closeNotificationPopup() {
    const popup = document.getElementById('notificationPopup');
    if (popup) {
        popup.querySelector('.popup-content')?.classList.remove('show');
        setTimeout(() => {
            popup.classList.remove('active');
        }, 300);
    }
}

/**
 * Tutup popup saat klik di luar konten
 */
document.addEventListener('click', function(event) {
    const popups = ['confirmPopup', 'notificationPopup'];

    popups.forEach(popupId => {
        const popup = document.getElementById(popupId);
        if (popup && event.target === popup) {
            popup.classList.remove('active');
            popup.querySelector('.popup-content')?.classList.remove('show');
        }
    });
});
