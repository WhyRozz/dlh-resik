// ===== STATE GLOBAL =====
let pendingForm = null;

// ===== LIVE SEARCH FUNCTION =====

/**
 * FUNGSI: Filter tabel penjemputan client-side berdasarkan nama admin, waktu, atau status
 * INTERAKSI: Auto-init saat DOM ready, dipanggil via oninput pada #liveSearchInput
 * PERFORMA: Debounce 300ms untuk mengurangi reflow saat typing
 */
function initLiveSearch() {
    const input = document.getElementById('liveSearchInput');
    const clearBtn = document.getElementById('clearSearch');
    const table = document.getElementById('penjemputanTable');
    let timer = null;

    if (input && clearBtn) {
        // Show clear button if input has value on load
        if (input.value.trim() !== '') clearBtn.style.display = 'inline-block';

        input.addEventListener('input', function () {
            const val = this.value.trim();
            clearBtn.style.display = val ? 'inline-block' : 'none';
            clearTimeout(timer);
            timer = setTimeout(() => filterTable(val), 300);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            clearBtn.style.display = 'none';
            filterTable('');
            input.focus();
        });
    }

    function filterTable(query) {
        if (!table) return;
        const filter = query.toLowerCase();
        const rows = table.getElementsByTagName('tr');
        for (let i = 1; i < rows.length; i++) {
            const tdNama = rows[i].getElementsByTagName('td')[2];
            const tdWilayah = rows[i].getElementsByTagName('td')[3];  
            const tdWaktu = rows[i].getElementsByTagName('td')[4];
            const tdStatus = rows[i].getElementsByTagName('td')[6];

            const namaVal = tdNama?.textContent.toLowerCase() || '';
            const wilayahVal = tdWilayah?.textContent.toLowerCase() || ''; 
            const waktuVal = tdWaktu?.textContent.toLowerCase() || '';
            const statusVal = tdStatus?.textContent.toLowerCase() || '';

            const match = namaVal.includes(filter) ||
                wilayahVal.includes(filter) || 
                waktuVal.includes(filter) ||
                statusVal.includes(filter);
            rows[i].style.display = match ? '' : 'none';
        }
    }
}

// ===== MODAL DETAIL =====
/**
 * FUNGSI: Fetch dan tampilkan detail penjemputan via AJAX
 * PARAM: {number} id - ID penjemputan yang akan ditampilkan
 * INTERAKSI: Dipanggil via onclick pada baris tabel
 */
function showDetail(id) {
    const modal = document.getElementById('detailModal');
    fetch(`/admin/bank-sampah/penjemputan/${id}/detail`)
        .then(response => response.json())
        .then(data => {
            console.log('🔍 Data dari API:', data);

            document.getElementById('modalNo').value = data.id;

            // ✅ NAMA PETUGAS (bukan nama_admin)
            document.getElementById('modalNamaPetugas').value = data.nama_petugas || data.nama_admin;

            // ✅ WILAYAH KERJA (BARU)
            document.getElementById('modalWilayahKerja').value = data.wilayah_kerja || 'Petugas DLH';

            document.getElementById('modalWaktu').value = new Date(data.waktu).toLocaleString('id-ID');
            document.getElementById('modalBerat').value = parseFloat(data.berat).toFixed(2) + ' Kg';
            document.getElementById('modalLokasi').value = data.lokasi || '-';
            document.getElementById('modalKeterangan').value = data.keterangan || '-';

            // ✅ BUILD URL GAMBAR
            const imgUrl = data.foto ? `/storage/${data.foto}` : '/images/no-image.png';
            document.getElementById('modalFoto').src = imgUrl;

            modal.style.display = 'flex';
        })
        .catch(error => {
            console.error('❌ ERROR:', error);
            alert('Gagal mengambil data detail.');
        });
}

/**
 * FUNGSI: Menutup modal detail penjemputan
 * INTERAKSI: Dipanggil via onclick pada tombol tutup atau klik di luar modal
 */
function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

// ===== MODAL KONFIRMASI =====

/**
 * FUNGSI: Tampilkan modal konfirmasi untuk approve/reject penjemputan
 * PARAM: {string} type - 'approve' atau 'reject'
 * PARAM: {number} id - ID penjemputan target
 * INTERAKSI: Dipanggil via onclick pada tombol approve/reject
 */
function showConfirm(type, id) {
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('confirmTitle');
    const msg = document.getElementById('confirmMessage');
    const btn = document.getElementById('confirmYesBtn');
    const formId = type === 'approve' ? `form-approve-${id}` : `form-reject-${id}`;
    pendingForm = document.getElementById(formId);
    if (!pendingForm) return;

    if (type === 'approve') {
        title.innerText = 'Konfirmasi Penjemputan';
        msg.innerHTML = 'Apakah Anda yakin ingin <b>menyetujui</b> penjemputan ini?<br>Data akan ditandai sebagai Disetujui.';
        btn.innerText = 'Ya, Setujui';
        btn.style.background = '#43a047';
    } else {
        title.innerText = 'Konfirmasi Penolakan';
        msg.innerHTML = 'Apakah Anda yakin ingin <b>menolak</b> penjemputan ini?<br>Data akan ditandai sebagai Ditolak.';
        btn.innerText = 'Ya, Tolak';
        btn.style.background = '#e53935';
    }
    modal.style.display = 'flex';
}

/**
 * FUNGSI: Menutup modal konfirmasi dan reset state
 * INTERAKSI: Dipanggil via onclick pada tombol batal atau klik di luar modal
 */
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    pendingForm = null;
}

// ===== RESET FILTER =====

/**
 * FUNGSI: Reset filter query string dan reload halaman
 * INTERAKSI: Dipanggil via onclick pada tombol reset filter
 */
function resetFilter() {
    const url = new URL(window.location);
    url.searchParams.delete('bulan');
    url.searchParams.delete('tahun');
    url.searchParams.delete('status');
    url.searchParams.delete('kecamatan_id');
    url.searchParams.delete('desa_id');
    window.location.href = url.pathname;
}

// ===== SUCCESS POPUP =====

/**
 * FUNGSI: Tampilkan popup sukses dengan pesan dari session Laravel
 * PARAM: {string} message - Pesan sukses yang akan ditampilkan
 * INTERAKSI: Dipanggil via bridge dari Blade setelah submit berhasil
 */
function showSuccessPopup(message) {
    const popup = document.getElementById('successPopup');
    const msg = document.getElementById('successMessage');
    if (popup && msg) {
        msg.textContent = message || 'Data penjemputan telah diperbarui.';
        popup.style.display = 'flex';
        setTimeout(() => { popup.style.display = 'none'; }, 3000);
    }
}


// ===== CASCADING DROPDOWN KECAMATAN → DESA (BARU!) =====
function initCascadingFilter() {
    const kecamatanSelect = document.getElementById('filterKecamatan');
    const desaSelect = document.getElementById('filterDesa');

    if (!kecamatanSelect || !desaSelect) return;

    // ✅ Jika ada kecamatan_id dari URL (setelah filter), load desa-nya
    const urlParams = new URLSearchParams(window.location.search);
    const kecamatanId = urlParams.get('kecamatan_id');
    const desaId = urlParams.get('desa_id');

    if (kecamatanId) {
        loadDesaByKecamatan(kecamatanId, desaId);
    }

    // ✅ Event listener saat kecamatan berubah
    kecamatanSelect.addEventListener('change', function () {
        const selectedKecId = this.value;
        if (!selectedKecId) {
            desaSelect.innerHTML = '<option value="">Semua Desa</option>';
            desaSelect.disabled = true;
            return;
        }
        loadDesaByKecamatan(selectedKecId);
    });
}

function loadDesaByKecamatan(kecamatanId, selectedDesaId = null) {
    const desaSelect = document.getElementById('filterDesa');
    if (!desaSelect) return;

    desaSelect.disabled = true;
    desaSelect.innerHTML = '<option value="">Memuat...</option>';

    fetch(`/admin/data-pengguna/desa/${kecamatanId}`)
        .then(response => response.json())
        .then(data => {
            desaSelect.innerHTML = '<option value="">Semua Desa</option>';
            data.forEach(desa => {
                const option = document.createElement('option');
                option.value = desa.id_desa;
                option.textContent = desa.nama_desa;
                if (selectedDesaId && desa.id_desa == selectedDesaId) {
                    option.selected = true;
                }
                desaSelect.appendChild(option);
            });
            desaSelect.disabled = false;
        })
        .catch(error => {
            console.error('❌ Error loading desa:', error);
            desaSelect.innerHTML = '<option value="">Error loading desa</option>';
        });
}


// ===== GLOBAL EVENT LISTENERS =====
/**
 * FUNGSI: Setup global event listeners saat DOM ready
 * INTERAKSI: Auto-trigger saat halaman selesai load
 */
document.addEventListener('DOMContentLoaded', function () {
    // Init live search
    initLiveSearch();

    initCascadingFilter();

    // Close modal on outside click
    window.onclick = function (event) {
        if (event.target === document.getElementById('detailModal')) closeModal();
        if (event.target === document.getElementById('confirmModal')) closeConfirmModal();
        if (event.target === document.getElementById('successPopup')) {
            document.getElementById('successPopup').style.display = 'none';
        }
    };

    // Close modal on ESC key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
            closeConfirmModal();
            document.getElementById('successPopup').style.display = 'none';
        }
    });

    // Confirm button click handler
    document.getElementById('confirmYesBtn')?.addEventListener('click', function () {
        if (pendingForm) pendingForm.submit();
    });

    // Handle success popup from Laravel session via bridge
    // Logic ini dipanggil dari Blade via window.PenjemputanConfig
    if (window.PenjemputanConfig?.successMessage) {
        showSuccessPopup(window.PenjemputanConfig.successMessage);
    }
    if (window.PenjemputanConfig?.errorMessage) {
        alert(window.PenjemputanConfig.errorMessage);
    }
});


// Ekspos fungsi ke global
window.showDetail = showDetail;
window.closeModal = closeModal;
window.showConfirm = showConfirm;
window.closeConfirmModal = closeConfirmModal;