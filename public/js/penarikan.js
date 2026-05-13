/**
 * Data Penarikan - Main JavaScript
 */

let currentId = null;
let currentStatus = null;

// ================= UTILITY FUNCTIONS =================

// Format Rupiah
const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
};

// ================= FILTER FUNCTIONS =================

function resetFilter() {
    const url = new URL(window.location);
    url.searchParams.delete('bulan');
    url.searchParams.delete('tahun');
    url.searchParams.delete('status');
    url.searchParams.delete('search');
    window.location.href = url.pathname;
}

// ================= LIVE SEARCH =================

(function initLiveSearch() {
    const input = document.getElementById('searchInput');
    const table = document.getElementById('penarikanTable');
    let timer = null;

    if (input && table) {
        input.addEventListener('input', function() {
            const val = this.value.trim();
            clearTimeout(timer);
            timer = setTimeout(() => filterTable(val), 300);
        });
    }

    function filterTable(query) {
        if (!table) return;
        const filter = query.toLowerCase();
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const text = rows[i].textContent.toLowerCase();
            rows[i].style.display = text.includes(filter) ? '' : 'none';
        }
    }
})();

// ================= MODAL DETAIL =================

function showDetail(id) {
    currentId = id;

    fetch(`/admin/bank-sampah/penarikan/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set values
            const elId = document.getElementById('detail-id');
            const elNama = document.getElementById('detail-nama');
            const elTanggal = document.getElementById('detail-tanggal');
            const elJumlah = document.getElementById('detail-jumlah');
            const elJenis = document.getElementById('detail-jenis');
            const elEwallet = document.getElementById('detail-ewallet');
            const elStatusText = document.getElementById('detail-status-text');
            const elStatus = document.getElementById('detail-status');

            if (elId) elId.value = '#TRX-' + String(data.id_penarikan).padStart(5, '0');
            if (elNama) elNama.value = data.nama_user || 'Unknown';
            if (elTanggal) elTanggal.value = new Date(data.tanggal_penarikan).toLocaleString('id-ID');
            if (elJumlah) elJumlah.value = formatRupiah(data.jumlah_uang);
            if (elJenis) elJenis.value = (data.jenis_ewallet || '-').toUpperCase();
            if (elEwallet) elEwallet.value = data.nomor_ewallet || '-';
            if (elStatusText) elStatusText.value = data.status.toUpperCase();
            if (elStatus) elStatus.value = data.status;

            // Save current status
            currentStatus = data.status;

            // Show/hide elements based on status
            const statusFinalInfo = document.getElementById('statusFinalInfo');
            const btnSimpan = document.getElementById('btnSimpan');
            const alasanGroup = document.getElementById('alasanPenolakanGroup');

            if (data.status !== 'diproses') {
                if (statusFinalInfo) statusFinalInfo.style.display = 'block';
                if (btnSimpan) btnSimpan.style.display = 'none';
            } else {
                if (statusFinalInfo) statusFinalInfo.style.display = 'none';
                if (btnSimpan) btnSimpan.style.display = 'inline-block';
            }

            // Fill alasan if exists
            if (alasanGroup && data.alasan_penolakan) {
                document.getElementById('detail-alasan').value = data.alasan_penolakan;
            }

            toggleStatusInfo();

            // Show modal
            const modal = document.getElementById('detailModal');
            if (modal) {
                modal.classList.add('active');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengambil data detail');
        });
}

function closeModal() {
    const modal = document.getElementById('detailModal');
    if (modal) {
        modal.classList.remove('active');
    }
    currentId = null;
}

// ================= STATUS INFO TOGGLE =================

function toggleStatusInfo() {
    const statusSelect = document.getElementById('detail-status');
    if (!statusSelect) return;

    const status = statusSelect.value;
    const infoBox = document.getElementById('statusInfo');
    const alasanGroup = document.getElementById('alasanPenolakanGroup');

    // Show/hide alasan field
    if (alasanGroup) {
        alasanGroup.style.display = (status === 'ditolak') ? 'block' : 'none';
    }

    const messages = {
        'diproses': {
            text: '🔄 Status masih dalam proses verifikasi',
            class: 'info'
        },
        'berhasil': {
            text: '✅ Penarikan disetujui. Saldo sudah dipotong dan admin akan melakukan transfer manual.',
            class: 'success'
        },
        'ditolak': {
            text: '❌ Penarikan ditolak. Saldo akan dikembalikan otomatis ke anggota.',
            class: 'danger'
        }
    };

    if (infoBox && messages[status]) {
        infoBox.textContent = messages[status].text;
        infoBox.className = 'status-info active ' + messages[status].class;
    }
}

// ================= UPDATE STATUS =================

function updateStatus() {
    if (!currentId) {
        alert('ID penarikan tidak ditemukan');
        return;
    }

    const statusSelect = document.getElementById('detail-status');
    const alasanInput = document.getElementById('detail-alasan');
    
    if (!statusSelect) return;

    const status = statusSelect.value;

    // Validate alasan for rejection
    if (status === 'ditolak' && (!alasanInput || !alasanInput.value.trim())) {
        alert('❌ Alasan penolakan wajib diisi!');
        if (alasanInput) alasanInput.focus();
        return;
    }

    if (!confirm('Yakin ingin mengubah status menjadi ' + status.toUpperCase() + '?')) {
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
        alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
        location.reload();
        return;
    }

    // Prepare request body
    const requestBody = {
        status: status,
        _method: 'PUT'
    };

    if (alasanInput) {
        requestBody.alasan_penolakan = alasanInput.value;
    }

    fetch(`/admin/bank-sampah/penarikan/${currentId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestBody)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessPopup(data.message);
            setTimeout(() => {
                location.reload();
            }, 2500);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Gagal: ' + error.message);
    });
}

// ================= SUCCESS POPUP =================

function showSuccessPopup(message) {
    const popup = document.getElementById('successPopup');
    const msg = document.getElementById('successMessage');
    
    if (popup && msg) {
        msg.textContent = message || 'Data berhasil diperbarui!';
        popup.style.display = 'flex';
        
        setTimeout(() => {
            popup.style.display = 'none';
        }, 2500);
    }
}

// ================= EVENT LISTENERS =================

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const detailModal = document.getElementById('detailModal');
    const successPopup = document.getElementById('successPopup');

    if (detailModal) {
        detailModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    if (successPopup) {
        successPopup.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }

    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            if (successPopup) successPopup.style.display = 'none';
        }
    });
});

// ================= LARAVEL SESSION HANDLER =================

// Handle success/error messages from Laravel session
document.addEventListener('DOMContentLoaded', function() {
    // This would be populated by Blade if needed
    // For now, handled via showSuccessPopup()
});