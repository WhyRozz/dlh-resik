// ===== STATE GLOBAL =====
let deleteId = null;

// FUNGSI INI: Tampilkan SweetAlert konfirmasi hapus
function showDeleteModal(id) {
    deleteId = id;  // Simpan ID ke variabel global
    
    // Langsung tampilkan SweetAlert konfirmasi
    Swal.fire({
        title: 'Hapus Artikel?',
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
            confirmDelete();  // Panggil fungsi hapus yang sudah ada
        }
    });
}

/**
 * Eksekusi hapus via AJAX (TANPA konfirmasi SweetAlert lagi)
 */
function confirmDelete() {
    if (!deleteId) return;

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
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Artikel berhasil dihapus!',
                timer: 2000,
                showConfirmButton: false,
                position: 'center'
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal!', data.message || 'Gagal menghapus artikel', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'Terjadi kesalahan saat menghapus artikel', 'error');
    });
}

/**
 * Tampilkan modal sukses
 */
function showSuccessModal(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message || 'Data berhasil disimpan.',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: 'center'
    });
}

// ========================================
// SEARCH LIVE FILTER (FIXED)
// ========================================

/**
 * Live Search: Filter artikel berdasarkan judul (real-time)
 */
function filterArtikel() {
    const input = document.getElementById('searchArtikel');
    const clearBtn = document.getElementById('clearSearch');
    const filter = input?.value.toLowerCase().trim() || '';
    
    // ✅ PENTING: Gunakan .data-table sesuai HTML
    const table = document.querySelector('.data-table');
    if (!table) {
        console.warn('Tabel .data-table tidak ditemukan!');
        return;
    }
    
    const tbody = table.querySelector('tbody');
    const rows = tbody?.querySelectorAll('tr') || [];
    
    // Toggle clear button visibility
    if (clearBtn) {
        clearBtn.style.display = filter.length > 0 ? 'block' : 'none';
    }
    
    let visibleCount = 0;
    
    // Loop semua baris tabel
    rows.forEach(row => {
        // Skip row empty state
        if (row.querySelector('.empty-state')) {
            row.style.display = '';
            return;
        }
        
        // Skip row "tidak ditemukan" (nanti kita handle terpisah)
        if (row.classList.contains('empty-search')) {
            return;
        }
        
        // Ambil teks dari kolom Judul (index 2: 0=No, 1=Gambar, 2=Judul)
        const judulCell = row.cells[2];
        const judulText = judulCell?.textContent?.toLowerCase() || '';
        
        // Filter: tampilkan jika cocok, sembunyikan jika tidak
        if (judulText.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Handle pesan "tidak ditemukan"
    const emptyRow = tbody.querySelector('.empty-search');
    
    if (visibleCount === 0 && filter.length > 0) {
        // Tampilkan pesan jika tidak ada hasil & ada keyword
        if (!emptyRow) {
            const tr = document.createElement('tr');
            tr.className = 'empty-search';
            tr.innerHTML = `<td colspan="5" class="text-center py-4 text-muted">Artikel tidak ditemukan</td>`;
            tbody.appendChild(tr);
        }
    } else if (emptyRow && (visibleCount > 0 || filter.length === 0)) {
        // Hapus pesan jika ada hasil atau search dikosongkan
        emptyRow.remove();
    }
}

/**
 * Clear search input
 */
function clearSearch() {
    const input = document.getElementById('searchArtikel');
    const clearBtn = document.getElementById('clearSearch');
    
    if (input) {
        input.value = '';
        input.focus();
    }
    if (clearBtn) {
        clearBtn.style.display = 'none';
    }
    
    filterArtikel(); // Refresh filter setelah clear
}
    
    // 1. Search Event Listeners
    const searchInput = document.getElementById('searchArtikel');
    const clearBtn = document.getElementById('clearSearch');
    
    // Live search via onkeyup (sudah di HTML: onkeyup="filterArtikel()")
    // Tapi kita tambah listener juga untuk keamanan
    if (searchInput) {
        searchInput.addEventListener('input', filterArtikel);
    }
    
    // Clear button click
    if (clearBtn) {
        clearBtn.addEventListener('click', clearSearch);
    }
    
    console.log('✅ Artikel JS loaded - Search live ready!');
