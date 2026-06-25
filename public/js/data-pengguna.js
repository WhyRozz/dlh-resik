/**
 * Data Pengguna - Modal & API Handler
 * File: public/js/data-pengguna.js
 */

// ===== STATE & ELEMENT REFERENCES =====
let searchTimeout = null;
let tableContainer = null;
let searchInput = null;

// ===== INIT FUNCTIONS =====
document.addEventListener('DOMContentLoaded', function () {
    initModalEvents();
    initLiveSearch();
});

// ===== FORMAT HELPER =====
/**
 * Format tanggal ke format Indonesia DD-MM-YYYY
 */
function formatTanggal(dateString) {
    if (!dateString) return '-';

    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return `${day}-${month}-${year}`;
}

// ===== MODAL EVENTS =====
/**
 * Setup event listeners untuk modal (outside click & ESC key)
 */
function initModalEvents() {
    const modal = document.getElementById('userModal');

    if (modal) {
        // Close modal when clicking outside
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    // Close modal with ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

// ===== OPEN MODAL =====
/**
 * Open modal and fetch user detail via API
 * @param {string|number} userId - User ID
 * @param {string} userType - 'pns' or 'masyarakat'
 */
function openModal(userId, userType) {
    // Validate input
    if (!userId || !userType) {
        console.error('Invalid userId or userType:', userId, userType);
        alert('Data pengguna tidak valid');
        return;
    }

    // Clean userId
    userId = String(userId).trim();

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const url = `/admin/data-pengguna/api/${userType}/${userId}`;

    console.log('Fetching from:', url);

    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token || '',
            'Accept': 'application/json',
        },
    })
        .then(response => {
            console.log('Response status:', response.status);

            if (response.status === 404) {
                throw new Error('Data pengguna tidak ditemukan');
            }

            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }

            return response.json();
        })
        .then(data => {
            console.log('User data:', data);

            // Fill modal fields - DATA PRIBADI
            document.getElementById('modalNama').textContent = data.nama || '-';
            document.getElementById('modalJenisKelamin').textContent = data.jenis_kelamin || '-';
            document.getElementById('modalEmail').textContent = data.email || '-';
            document.getElementById('modalTelp').textContent = data.no_telepon || '-';
            document.getElementById('modalTglLahir').textContent = formatTanggal(data.tanggal_lahir);
            document.getElementById('modalAlamat').textContent = data.alamat || '-';

            // Fill modal fields - PEKERJAAN, WILAYAH & ACCOUNT INFO BERDASARKAN TIPE
            if (userType === 'pns') {
                // PNS: Tampilkan "ASN/PNS (Nama Dinas)", hilangkan Kecamatan & Desa
                document.getElementById('modalPekerjaan').textContent = `ASN/PNS (${data.nama_dinas || '-'})`;
                document.getElementById('modalKecamatan').textContent = '-';
                document.getElementById('modalDesa').textContent = '-';

                // PNS tetap menampilkan Kode Anggota & Barcode ID dari data
                document.getElementById('modalKodeAnggota').textContent = data.kode_anggota || '-';
                document.getElementById('modalBarcodeId').textContent = data.barcode_id || '-';
            } else {
                // MASYARAKAT: Tampilkan "Masyarakat Umum", Kecamatan & Desa aktif
                document.getElementById('modalPekerjaan').textContent = 'Masyarakat Umum';
                document.getElementById('modalKecamatan').textContent = data.nama_kecamatan || '-';
                document.getElementById('modalDesa').textContent = data.nama_desa || '-';

                // MASYARAKAT: Sembunyikan Kode Anggota, pindahkan kode KANN ke Barcode ID
                document.getElementById('modalKodeAnggota').textContent = '-';
                document.getElementById('modalBarcodeId').textContent = data.kode_anggota || '-';
            }
            document.getElementById('modalSaldo').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.saldo || 0);
            document.getElementById('modalCreated').textContent = formatTanggal(data.created_at);

            // Show modal
            document.getElementById('userModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
}

// ===== CLOSE MODAL =====
/**
 * Close user detail modal
 */
function closeModal() {
    const modal = document.getElementById('userModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// ===== LIVE SEARCH =====
/**
 * Setup live search dengan debounce 500ms
 */
function initLiveSearch() {
    searchInput = document.querySelector('.search-input');
    tableContainer = document.querySelector('.table-container');

    if (!searchInput || !tableContainer) return;

    searchInput.addEventListener('input', function (e) {
        const searchValue = e.target.value.trim();
        const filterInput = document.querySelector('input[name="filter"]');
        const currentFilter = filterInput?.value || 'all';

        // Clear timeout sebelumnya untuk mencegah multiple request
        clearTimeout(searchTimeout);

        // Tunggu 500ms setelah user berhenti mengetik sebelum fetch
        searchTimeout = setTimeout(function () {
            performSearch(searchValue, currentFilter);
        }, 500);
    });
}

/**
 * Perform search via AJAX dan update tabel
 */
function performSearch(search, filter) {
    // Simpan konten asli untuk fallback jika error
    const originalContent = tableContainer.innerHTML;

    // Tampilkan loading spinner saat fetch berjalan
    tableContainer.innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #2e8b57;"></i>
            <p style="margin-top: 15px; color: #666;">Mencari...</p>
        </div>
    `;

    // Build URL dengan parameter search dan filter via bridge config
    const baseUrl = window.DataPenggunaConfig?.routes?.index || '/admin/data-pengguna';
    const url = `${baseUrl}?search=${encodeURIComponent(search)}&filter=${filter}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
        .then(response => response.text())
        .then(html => {
            // Parse HTML response dari server
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableContainer = doc.querySelector('.table-container');

            if (newTableContainer) {
                // Replace konten tabel dengan hasil search
                tableContainer.innerHTML = newTableContainer.innerHTML;
                // Re-cache elements after DOM update
                tableContainer = document.querySelector('.table-container');
                searchInput = document.querySelector('.search-input');
                // Re-init live search after DOM update
                if (searchInput) initLiveSearch();
            } else {
                // Restore konten asli jika parsing gagal
                tableContainer.innerHTML = originalContent;
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            // Restore konten asli jika terjadi error network
            tableContainer.innerHTML = originalContent;
        });
}

// ===== FILTER WILAYAH & DINAS =====
function toggleFilterType() {
    const tipeFilter = document.getElementById('tipeFilter').value;
    const filterWilayah = document.getElementById('filterWilayah');
    const filterDinas = document.getElementById('filterDinas');

    if (tipeFilter === 'dinas') {
        filterWilayah.style.display = 'none';
        filterDinas.style.display = 'inline-flex';
    } else {
        filterDinas.style.display = 'none';
        filterWilayah.style.display = 'inline-flex';
    }

    toggleResetWilayah();
}

function toggleResetWilayah() {
    const btnResetWilayah = document.getElementById('btnResetWilayah');
    const kecId = document.getElementById('filterKecamatan')?.value || '';
    const desaId = document.getElementById('filterDesa')?.value || '';
    const dinasId = document.getElementById('filterDinasSelect')?.value || '';

    if (kecId || desaId || dinasId) {
        btnResetWilayah.style.display = 'inline-flex';
    } else {
        btnResetWilayah.style.display = 'none';
    }
}

// Cascading dropdown: Kecamatan → Desa
document.addEventListener('DOMContentLoaded', function() {
    const kecamatanSelect = document.getElementById('filterKecamatan');
    if (kecamatanSelect) {
        kecamatanSelect.addEventListener('change', function() {
            const kecId = this.value;
            const desaSelect = document.getElementById('filterDesa');
            
            desaSelect.innerHTML = '<option value="">Semua Desa</option>';
            desaSelect.disabled = !kecId;

            if (kecId) {
                fetch(`/admin/data-pengguna/desa/${kecId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(d => {
                            const option = document.createElement('option');
                            option.value = d.id_desa;
                            option.textContent = d.nama_desa;
                            desaSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error('Error fetching desa:', err));
            }
            
            toggleResetWilayah();
        });
    }

    // Tombol Filter Wilayah
    const btnFilter = document.getElementById('btnFilterWilayah');
    if (btnFilter) {
        btnFilter.addEventListener('click', function() {
            const tipeFilter = document.getElementById('tipeFilter').value;
            const url = new URL(window.location.href);

            url.searchParams.set('tipe_filter', tipeFilter);

            if (tipeFilter === 'wilayah') {
                const kecId = document.getElementById('filterKecamatan').value;
                const desaId = document.getElementById('filterDesa').value;

                if (kecId) url.searchParams.set('kecamatan_id', kecId);
                else url.searchParams.delete('kecamatan_id');

                if (desaId) url.searchParams.set('desa_id', desaId);
                else url.searchParams.delete('desa_id');

                url.searchParams.delete('dinas_id');
            } else if (tipeFilter === 'dinas') {
                const dinasId = document.getElementById('filterDinasSelect').value;

                if (dinasId) url.searchParams.set('dinas_id', dinasId);
                else url.searchParams.delete('dinas_id');

                url.searchParams.delete('kecamatan_id');
                url.searchParams.delete('desa_id');
            }

            window.location.href = url.toString();
        });
    }

    // Tombol Reset Wilayah
    const btnReset = document.getElementById('btnResetWilayah');
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            const url = new URL(window.location.href);
            url.searchParams.delete('kecamatan_id');
            url.searchParams.delete('desa_id');
            url.searchParams.delete('dinas_id');
            url.searchParams.delete('tipe_filter');
            window.location.href = url.toString();
        });
    }

    // Init on load
    toggleFilterType();
    toggleResetWilayah();
});

// ===== EXPORT FUNCTIONS =====
window.DataPenggunaJS = {
    openModal: openModal,
    closeModal: closeModal,
    formatTanggal: formatTanggal,
    performSearch: performSearch
};