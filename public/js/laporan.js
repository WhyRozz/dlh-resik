/**
 * Script untuk halaman Kelola Laporan Admin
 */

document.addEventListener('DOMContentLoaded', function() {
    // Live Search Functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr:not(.detail-row):not(:last-child)');

            rows.forEach(row => {
                const nama = row.cells[1]?.textContent.toLowerCase() || '';
                const lokasi = row.cells[2]?.textContent.toLowerCase() || '';
                row.style.display = (nama.includes(query) || lokasi.includes(query)) ? '' : 'none';
            });
        });
    }
});

/**
 * Toggle detail row visibility
 * @param {number} id - ID laporan
 */
function toggleDetail(id) {
    const detail = document.getElementById(`detail-${id}`);
    const allDetails = document.querySelectorAll('.detail-row');

    allDetails.forEach(d => {
        if (d.id !== `detail-${id}`) {
            d.classList.remove('active');
        }
    });

    if (detail) {
        detail.classList.toggle('active');
    }
}

/**
 * Close detail row
 * @param {number} id - ID laporan
 */
function closeDetail(id) {
    const detail = document.getElementById(`detail-${id}`);
    if (detail) {
        detail.classList.remove('active');
    }
}

/**
 * Handle status change - enable/disable balasan textarea
 * @param {number} id - ID laporan
 */
function onStatusChange(id) {
    const selected = document.querySelector(`input[name="status-${id}"]:checked`);
    const textarea = document.getElementById(`balasan-${id}`);

    if (selected && textarea) {
        // Enable textarea hanya jika status Diterima atau Ditolak
        textarea.disabled = !(selected.value === 'Diterima' || selected.value === 'Ditolak');
    }
}

/**
 * Update status laporan via AJAX
 * @param {number} id - ID laporan
 */
function updateStatus(id) {
    const selected = document.querySelector(`input[name="status-${id}"]:checked`);

    if (!selected) {
        alert('Pilih status terlebih dahulu.');
        return;
    }

    const status = selected.value;
    const balasanInput = document.getElementById(`balasan-${id}`);
    const balasan = balasanInput ? balasanInput.value.trim() : '';

    // Validasi: tidak boleh ada balasan jika status masih Diproses
    if (status === 'Diproses' && balasan) {
        const popupWarning = document.getElementById('popupWarning');
        if (popupWarning) {
            popupWarning.style.display = 'flex';
        }
        return;
    }

    // Validasi panjang balasan
    if ((status === 'Diterima' || status === 'Ditolak') && balasan.length > 500) {
        alert('Balasan terlalu panjang (maksimal 500 karakter).');
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Kirim request
    fetch('/admin/laporan/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `id=${id}&status=${encodeURIComponent(status)}&balasan=${encodeURIComponent(balasan)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const popupSuccess = document.getElementById('popupSuccess');
            if (popupSuccess) {
                popupSuccess.style.display = 'flex';
            }
            closeDetail(id);
            setTimeout(() => location.reload(), 300);
        } else {
            alert(data.message || 'Gagal menyimpan data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan koneksi.');
    });
}

/**
 * Close warning popup
 */
function closePopupWarning() {
    const popup = document.getElementById('popupWarning');
    if (popup) {
        popup.style.display = 'none';
    }
}

/**
 * Close success popup
 */
function closePopupSuccess() {
    const popup = document.getElementById('popupSuccess');
    if (popup) {
        popup.style.display = 'none';
    }
}

/**
 * Close popup when clicking outside
 */
document.addEventListener('click', function(event) {
    const popups = ['popupWarning', 'popupSuccess'];

    popups.forEach(popupId => {
        const popup = document.getElementById(popupId);
        if (popup && event.target === popup) {
            popup.style.display = 'none';
        }
    });
});
