/**
 * Script untuk halaman Kelola TPS Admin
 * File: public/js/tps.js
 * Fitur: SweetAlert + Live Search
 */

// ===== STATE GLOBAL =====
let deleteId = null; // Simpan ID saat hapus

// ===== FUNGSI: SweetAlert Konfirmasi Hapus =====
function konfirmasiHapus(id) {
    Swal.fire({
        title: 'Hapus TPS?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        position: 'center'
    }).then((result) => {
        if (result.isConfirmed) {
            // ✅ AJAX Fetch ke server
            fetch(`/admin/tps/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',  // ✅ PENTING: minta response JSON
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())  // ✅ Parse response sebagai JSON
            .then(data => {
                if (data.success) {
                    // ✅ Tampilkan SweetAlert sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Data TPS berhasil dihapus!',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        position: 'center'
                    }).then(() => {
                        // ✅ Redirect setelah SweetAlert tutup
                        window.location.href = '/admin/tps';
                    });
                } else {
                    // ✅ Tampilkan error dari server
                    Swal.fire('Gagal!', data.message || 'Gagal menghapus data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus data'
                });
            });
        }
    });
}

// ===== FUNGSI: Live Search Filter =====
function filterTps() {
    const input = document.getElementById('searchInput');
    const filter = input?.value.toLowerCase().trim() || '';
    
    // Loop semua baris tabel
    document.querySelectorAll('tbody tr').forEach(row => {
        // Skip baris empty state
        if (row.querySelector('.empty-state')) {
            row.style.display = '';
            return;
        }
        
        // Ambil teks dari kolom Nama (index 1) dan Lokasi (index 2)
        const nama = row.cells[1]?.textContent.toLowerCase() || '';
        const lokasi = row.cells[2]?.textContent.toLowerCase() || '';
        
        // Tampilkan jika cocok, sembunyikan jika tidak
        if (nama.includes(filter) || lokasi.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// ===== EVENT LISTENERS (DOMContentLoaded) =====
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Live Search: dengarkan input user
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterTps); // Filter real-time
    }
    
    // 2. Clear search jika ada (opsional)
    const clearBtn = document.getElementById('clearSearch');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            filterTps();
        });
    }
    
    console.log('✅ TPS JS loaded - SweetAlert + Search ready!');
});