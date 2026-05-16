/**
 * Script untuk halaman Kelola TPS Admin
 * File: public/js/tps.js
 */

// ===== STATE GLOBAL =====
// fungsi untuk menyimpan ID TPS yang akan dihapus (dipakai bersama oleh beberapa fungsi)
let idYangAkanDihapus = null;

// ===== INIT =====
// fungsi utama: jalankan semua inisialisasi saat halaman selesai load
document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Search: filter tabel TPS berdasarkan nama atau lokasi
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        // fungsi: dengarkan setiap ketikan user di input search
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            // fungsi: loop semua baris tabel untuk dicocokkan dengan keyword
            document.querySelectorAll('#tpsTableBody tr').forEach(row => {
                if (row.cells.length < 2) return; // skip baris "empty state"
                const nama = row.cells[1]?.textContent.toLowerCase() || '';
                const lokasi = row.cells[2]?.textContent.toLowerCase() || '';
                // fungsi: tampilkan baris jika cocok, sembunyikan jika tidak
                row.style.display = (nama.includes(query) || lokasi.includes(query)) ? '' : 'none';
            });
        });
    }

    // 2. Success from URL params: tampilkan popup jika redirect dengan ?success=...
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        // fungsi: mapping tipe success ke pesan yang sesuai
        const map = { 
            tambah: 'Data TPS berhasil ditambahkan.', 
            edit: 'Data TPS berhasil diperbarui.', 
            hapus: 'Data TPS berhasil dihapus.' 
        };
        // fungsi: tampilkan popup sukses dengan pesan yang sesuai
        showSuccessPopup(map[urlParams.get('success')] || 'Operasi berhasil.');
        // fungsi: bersihkan URL agar parameter success tidak muncul lagi saat refresh
        history.replaceState({}, '', window.location.pathname + window.location.hash);
    }

    // 3. Error/Success from bridge config: baca pesan dari Blade via window.TpsConfig
    // fungsi: tampilkan error popup jika ada validation error dari Laravel
    if (window.TpsConfig?.errorMessage) showErrorPopup(window.TpsConfig.errorMessage);
    // fungsi: tampilkan success popup jika ada session success dari Laravel
    if (window.TpsConfig?.successMessage) showSuccessPopup(window.TpsConfig.successMessage);

    // 4. Modal: Close on outside click
    // fungsi: tutup popup jika user klik di luar area konten modal
    document.addEventListener('click', function(e) {
        ['confirmPopup', 'successPopup', 'errorPopup'].forEach(id => {
            const popup = document.getElementById(id);
            if (popup && e.target === popup) {
                popup.querySelector('.popup-content')?.classList.remove('show');
                setTimeout(() => popup.classList.remove('active'), 300);
            }
        });
    });

    // 5. Modal: Close on ESC key
    // fungsi: tutup popup jika user tekan tombol ESC di keyboard
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['confirmPopup', 'successPopup', 'errorPopup'].forEach(id => {
                const popup = document.getElementById(id);
                if (popup) {
                    popup.querySelector('.popup-content')?.classList.remove('show');
                    setTimeout(() => popup.classList.remove('active'), 300);
                }
            });
        }
    });

    // 6. Modal Confirm Delete Button Handler: submit DELETE request via dynamic form
    // fungsi: handle klik tombol "Ya, Hapus" di modal konfirmasi
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (!idYangAkanDihapus) return; // fungsi: batal jika tidak ada ID yang dipilih
        
        // fungsi: disable tombol & ubah teks untuk feedback UX (mencegah double click)
        this.disabled = true;
        this.textContent = 'Menghapus...';
        
        // fungsi: buat form dinamis untuk submit request DELETE (karena browser hanya support GET/POST native)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/tps/${idYangAkanDihapus}`;
        
        // fungsi: tambahkan CSRF token + method spoofing agar Laravel menerima request DELETE
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        
        // fungsi: append form ke body dan submit
        document.body.appendChild(form);
        form.submit();
    });
});

// ===== POPUP FUNCTIONS =====

// fungsi: tampilkan modal konfirmasi hapus dan simpan ID TPS yang akan dihapus
function konfirmasiHapus(id) {
    idYangAkanDihapus = id;
    const popup = document.getElementById('confirmPopup');
    if (popup) {
        popup.classList.add('active');
        setTimeout(() => popup.querySelector('.popup-content')?.classList.add('show'), 10);
    }
}

// fungsi: tutup modal konfirmasi hapus dan reset variable ID
function closeConfirmPopup() {
    const popup = document.getElementById('confirmPopup');
    if (popup) {
        popup.querySelector('.popup-content')?.classList.remove('show');
        setTimeout(() => {
            popup.classList.remove('active');
            idYangAkanDihapus = null; // reset state agar tidak ada ID tertinggal
        }, 300);
    }
}

// fungsi: tampilkan popup sukses dengan pesan dinamis
function showSuccessPopup(message) {
    const popup = document.getElementById('successPopup');
    const messageEl = document.getElementById('successMessage');
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('active');
        setTimeout(() => popup.querySelector('.popup-content')?.classList.add('show'), 10);
    }
}

// fungsi: tutup popup sukses
function closeSuccessPopup() {
    const popup = document.getElementById('successPopup');
    if (popup) {
        popup.querySelector('.popup-content')?.classList.remove('show');
        setTimeout(() => popup.classList.remove('active'), 300);
    }
}

// fungsi: tampilkan popup error dengan pesan dari validation Laravel
function showErrorPopup(message) {
    const popup = document.getElementById('errorPopup');
    const messageEl = document.getElementById('errorMessage');
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('active');
        setTimeout(() => popup.querySelector('.popup-content')?.classList.add('show'), 10);
    }
}

// fungsi: tutup popup error
function closeErrorPopup() {
    const popup = document.getElementById('errorPopup');
    if (popup) {
        popup.querySelector('.popup-content')?.classList.remove('show');
        setTimeout(() => popup.classList.remove('active'), 300);
    }
}

// Export functions ke global window object (opsional, untuk debugging atau dipanggil dari Blade jika perlu)
window.TpsJS = {
    konfirmasiHapus, closeConfirmPopup, showSuccessPopup, closeSuccessPopup, 
    showErrorPopup, closeErrorPopup
};