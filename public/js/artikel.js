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








// ===== STATE GLOBAL =====
// FUNGSI: Menyimpan ID artikel yang akan dihapus (shared antar fungsi)
let deleteId = null;

// ===== MODAL: DELETE CONFIRMATION =====

/**
 * FUNGSI: Menampilkan modal konfirmasi hapus dan menyimpan ID artikel target
 * PARAM: {number} id - ID artikel yang akan dihapus dari database
 * INTERAKSI: Dipanggil via onclick pada tombol hapus di tabel artikel
 * KEAMANAN: ID hanya disimpan di variabel JS, validasi otorisasi tetap di server
 */
function showDeleteModal(id) {
    deleteId = id;
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.add('show');
}

/**
 * FUNGSI: Menyembunyikan modal konfirmasi hapus dan mereset state
 * INTERAKSI: Dipanggil saat user klik "Batal" atau klik di luar modal
 */
function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.remove('show');
    deleteId = null;
}

/**
 * FUNGSI: Mengeksekusi penghapusan artikel via AJAX DELETE request ke Laravel
 * KEAMANAN: 
 *   - Menggunakan CSRF token dari window.ArtikelConfig untuk mencegah CSRF attack
 *   - Request hanya dijalankan jika deleteId valid
 * INTERAKSI: Dipanggil saat user konfirmasi hapus di modal
 */
function confirmDelete() {
    if (!deleteId) return;

    // FUNGSI: Disable tombol dan ubah teks saat proses hapus berjalan (UX feedback)
    const deleteBtn = document.querySelector('#deleteModal .btn-hapus');
    const originalText = deleteBtn?.textContent || 'Hapus';
    if (deleteBtn) {
        deleteBtn.textContent = 'Menghapus...';
        deleteBtn.disabled = true;
    }

    // FUNGSI: Kirim request DELETE ke endpoint Laravel dengan CSRF protection
    fetch(`/admin/artikel/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.ArtikelConfig?.csrfToken || 
                           document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        hideDeleteModal();
        if (data.success) {
            // FUNGSI: Tampilkan modal sukses dan reload halaman setelah delay
            showSuccessModal(data.message || 'Artikel berhasil dihapus!');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.message || 'Gagal menghapus artikel');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideDeleteModal();
        alert('Terjadi kesalahan saat menghapus artikel');
    })
    .finally(() => {
        // FUNGSI: Restore state tombol setelah request selesai (cleanup)
        if (deleteBtn) {
            deleteBtn.textContent = originalText;
            deleteBtn.disabled = false;
        }
    });
}

// ===== MODAL: SUCCESS NOTIFICATION =====

/**
 * FUNGSI: Menampilkan modal sukses dengan pesan dinamis dan animasi
 * PARAM: {string} message - Pesan yang akan ditampilkan ke user
 * INTERAKSI: Dipanggil setelah operasi sukses (hapus, simpan, update)
 */
function showSuccessModal(message) {
    const messageEl = document.getElementById('successModalMessage');
    if (messageEl) messageEl.textContent = message;
    const modal = document.getElementById('successModal');
    if (modal) modal.classList.add('show');
}

/**
 * FUNGSI: Menyembunyikan modal sukses dan reset animasi
 * INTERAKSI: Dipanggil saat user klik tombol "Tutup"
 */
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) modal.classList.remove('show');
}

// ===== SEARCH: CLIENT-SIDE FILTER =====

/**
 * FUNGSI: Filter artikel client-side berdasarkan input judul (tanpa reload halaman)
 * INTERAKSI: Dipanggil via onkeyup pada input search di halaman
 * PERFORMA: Menggunakan loop sederhana, cocok untuk data < 1000 record
 * CATATAN: Untuk data besar, pertimbangkan server-side search via AJAX
 */
function filterArtikel() {
    const input = document.getElementById('searchArtikel');
    const filter = input?.value.toLowerCase() || '';
    const table = document.querySelector('.table-design');
    if (!table) return;
    
    const rows = table.getElementsByTagName('tr');
    
    // FUNGSI: Loop semua baris tabel (mulai dari index 1 untuk skip header)
    for (let i = 1; i < rows.length; i++) {
        // FUNGSI: Ambil kolom judul (index 2 karena: 0=No, 1=Gambar, 2=Judul)
        const td = rows[i].getElementsByTagName('td')[2];
        if (td) {
            const txtValue = td.textContent || td.innerText;
            // FUNGSI: Tampilkan baris jika cocok, sembunyikan jika tidak
            rows[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
        }
    }
}

// ===== EVENT LISTENERS: MODAL CLOSE & AUTO-INIT =====

/**
 * FUNGSI: Setup event listeners dan auto-init logic saat DOM ready
 * INTERAKSI: Auto-trigger saat halaman selesai load
 */
document.addEventListener('DOMContentLoaded', function() {
    // FUNGSI: Close delete modal on outside click (UX pattern umum)
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === this) hideDeleteModal();
        });
    }
    
    // FUNGSI: Close success modal on outside click
    const successModal = document.getElementById('successModal');
    if (successModal) {
        successModal.addEventListener('click', function(e) {
            if (e.target === this) closeSuccessModal();
        });
    }
    
    // FUNGSI: Auto-show success modal jika ada parameter success di URL
    // CATATAN: Untuk session Laravel, handle di Blade dengan window.ArtikelConfig.successMessage
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success') && window.ArtikelConfig?.successMessage) {
        showSuccessModal(window.ArtikelConfig.successMessage);
        // Bersihkan URL agar tidak muncul lagi saat refresh
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }
});