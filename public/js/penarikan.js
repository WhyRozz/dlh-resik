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
    url.searchParams.delete('kecamatan_id');
    url.searchParams.delete('desa_id');
    url.searchParams.delete('dinas_id');
    url.searchParams.delete('tipe_filter');
    window.location.href = url.pathname;
}

// ================= LIVE SEARCH =================

(function initLiveSearch() {
    const input = document.getElementById('searchInput');
    const table = document.getElementById('penarikanTable');
    let timer = null;

    if (input && table) {
        input.addEventListener('input', function () {
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
            const elTipe = document.getElementById('detail-tipe');
            const elTanggal = document.getElementById('detail-tanggal');
            const elJumlah = document.getElementById('detail-jumlah');
            const elJenis = document.getElementById('detail-jenis');
            const elEwallet = document.getElementById('detail-ewallet');
            const elStatusText = document.getElementById('detail-status-text');
            const elStatus = document.getElementById('detail-status');

            // Wilayah & Dinas elements
            const wilayahGroup = document.getElementById('detail-wilayah-group');
            const dinasGroup = document.getElementById('detail-dinas-group');
            const elKecamatan = document.getElementById('detail-kecamatan');
            const elDesa = document.getElementById('detail-desa');
            const elDinas = document.getElementById('detail-dinas');

            if (elId) elId.value = '#TRX-' + String(data.id_penarikan).padStart(5, '0');
            if (elNama) elNama.value = data.nama_user || 'Unknown';

            // Set tipe pengguna dan tampilkan field yang sesuai
            let tipePengguna = '';
            if (data.id_masyarakat) {
                tipePengguna = 'Masyarakat';
                // Tampilkan kecamatan & desa, sembunyikan dinas
                if (wilayahGroup) wilayahGroup.style.display = 'grid';
                if (dinasGroup) dinasGroup.style.display = 'none';

                // Set nilai kecamatan & desa
                if (elKecamatan) elKecamatan.value = data.masyarakat?.desa?.kecamatan?.nama_kecamatan || '-';
                if (elDesa) elDesa.value = data.masyarakat?.desa?.nama_desa || '-';
            } else if (data.id_pns) {
                tipePengguna = 'PNS';
                // Tampilkan dinas, sembunyikan kecamatan & desa
                if (wilayahGroup) wilayahGroup.style.display = 'none';
                if (dinasGroup) dinasGroup.style.display = 'block';

                // Set nilai dinas
                if (elDinas) elDinas.value = data.pns?.dinas?.nama_dinas || '-';
            }

            if (elTipe) elTipe.value = tipePengguna;

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
            showConfirmModal('Error', 'Gagal mengambil data detail', () => { }, 'danger');
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
            text: 'Status masih dalam proses verifikasi',
            class: 'info'
        },
        'berhasil': {
            text: 'Penarikan disetujui. Saldo sudah dipotong dan admin akan melakukan transfer manual.',
            class: 'success'
        },
        'ditolak': {
            text: 'Penarikan ditolak. Saldo akan dikembalikan otomatis ke pengguna.',
            class: 'danger'
        }
    };

    if (infoBox && messages[status]) {
        infoBox.textContent = messages[status].text;
        infoBox.className = 'status-info active ' + messages[status].class;
    }
}

// ================= CONFIRMATION MODAL =================

let confirmCallback = null;

function showConfirmModal(title, message, callback, type = 'warning') {
    const modal = document.getElementById('confirmModal');
    const confirmTitle = document.getElementById('confirmTitle');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmIcon = modal?.querySelector('.confirm-icon i');
    const btnOk = document.getElementById('btnConfirmOk');

    if (!modal) {
        if (confirm(message)) callback();
        return;
    }

    confirmTitle.textContent = title;
    confirmMessage.textContent = message;

    if (confirmIcon) {
        if (type === 'success') {
            confirmIcon.className = 'fas fa-check-circle';
            confirmIcon.parentElement.className = 'confirm-icon success';
            btnOk.className = 'btn-confirm btn-ok';
            btnOk.textContent = 'Ya, Setujui';
        } else if (type === 'danger') {
            confirmIcon.className = 'fas fa-times-circle';
            confirmIcon.parentElement.className = 'confirm-icon danger';
            btnOk.className = 'btn-confirm btn-ok danger';
            btnOk.textContent = 'Ya, Tolak';
        } else {
            confirmIcon.className = 'fas fa-question-circle';
            confirmIcon.parentElement.className = 'confirm-icon';
            btnOk.className = 'btn-confirm btn-ok';
            btnOk.textContent = 'Ya, Lanjutkan';
        }
    }

    confirmCallback = callback;
    modal.classList.add('active');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.remove('active');
    }
    confirmCallback = null;
}

function executeConfirm() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
}

// ================= UPDATE STATUS (BARU) =================

function updateStatus() {
    if (!currentId) {
        showConfirmModal('Error', 'ID penarikan tidak ditemukan', () => { }, 'danger');
        return;
    }

    const statusSelect = document.getElementById('detail-status');
    const alasanInput = document.getElementById('detail-alasan');

    if (!statusSelect) return;

    const status = statusSelect.value;

    // Validate alasan for rejection
    if (status === 'ditolak' && (!alasanInput || !alasanInput.value.trim())) {
        showConfirmModal('Validasi', '❌ Alasan penolakan wajib diisi!', () => {
            if (alasanInput) alasanInput.focus();
        }, 'danger');
        return;
    }

    // Prepare messages
    const messages = {
        'berhasil': {
            title: 'Konfirmasi Persetujuan',
            message: 'Yakin ingin mengubah status menjadi BERHASIL?\n\nSaldo sudah terpotong dan admin akan melakukan transfer manual.',
            type: 'success'
        },
        'ditolak': {
            title: 'Konfirmasi Penolakan',
            message: 'Yakin ingin mengubah status menjadi DITOLAK?\n\nSaldo akan dikembalikan otomatis ke pengguna.',
            type: 'danger'
        },
        'diproses': {
            title: 'Konfirmasi Update',
            message: 'Yakin ingin mengubah status menjadi DIPROSES?',
            type: 'warning'
        }
    };

    const config = messages[status] || messages['diproses'];

    showConfirmModal(
        config.title,
        config.message,
        () => submitStatusUpdate(status, alasanInput),
        config.type
    );
}

function submitStatusUpdate(status, alasanInput) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
        showConfirmModal('Error', 'CSRF token tidak ditemukan. Silakan refresh halaman.', () => location.reload(), 'danger');
        return;
    }

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
                closeConfirmModal();
                closeModal();
                showSuccessPopup(data.message);
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showConfirmModal('Error', '❌ ' + data.message, () => { }, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showConfirmModal('Error', '❌ Gagal: ' + error.message, () => { }, 'danger');
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
document.addEventListener('DOMContentLoaded', function () {
    const detailModal = document.getElementById('detailModal');
    const successPopup = document.getElementById('successPopup');

    if (detailModal) {
        detailModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    if (successPopup) {
        successPopup.addEventListener('click', function (e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }

    // ESC key to close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal();
            if (successPopup) successPopup.style.display = 'none';
        }
    });
});

// ================= LARAVEL SESSION HANDLER =================

// Handle success/error messages from Laravel session
document.addEventListener('DOMContentLoaded', function () {
    // This would be populated by Blade if needed
    // For now, handled via showSuccessPopup()
});