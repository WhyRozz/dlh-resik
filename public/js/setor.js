// ===== STATE & ELEMENT REFERENCES =====
// FUNGSI: Cache DOM elements untuk performance (hindari query berulang)
let tableContainer = null;
let filterForm = null;
let searchInput = null;
let clearBtn = null;
let resetBtn = null;
let timer = null;

// ===== INIT FUNCTION =====

/**
 * FUNGSI: Inisialisasi semua event listeners dan state saat DOM ready
 * INTERAKSI: Auto-trigger saat halaman selesai load
 */
function initSetorPage() {
    // Cache DOM elements
    tableContainer = document.querySelector('.table-container');
    filterForm = document.getElementById('filterForm');
    searchInput = document.getElementById('liveSearchInput');
    clearBtn = document.getElementById('clearSearch');
    resetBtn = document.getElementById('resetButton');

    // Init live search jika elemen ada
    if (searchInput && clearBtn) {
        initLiveSearch();
    }

    // Init filter form jika ada
    if (filterForm) {
        initFilterForm();
    }

    // Toggle reset button visibility on load
    toggleResetButton();
}

// ===== LIVE SEARCH =====

/**
 * FUNGSI: Setup event listeners untuk live search input
 * INTERAKSI: Dipanggil otomatis saat initSetorPage()
 * PERFORMA: Debounce 350ms untuk mengurangi request saat typing
 */
function initLiveSearch() {
    // Show clear button if input has value on load
    if (searchInput.value.trim() !== '') {
        clearBtn.style.display = 'inline-block';
    }

    // Input event dengan debounce
    searchInput.addEventListener('input', function () {
        const val = this.value.trim();

        // Toggle clear button visibility
        clearBtn.style.display = val ? 'inline-block' : 'none';

        // Toggle reset button based on filters
        toggleResetButton();

        // Debounce fetch
        clearTimeout(timer);
        timer = setTimeout(() => {
            if (val.length >= 2 || val === '') {
                fetchSearch(val);
            }
        }, 350);
    });

    // Clear button click handler
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        toggleResetButton();
        fetchSearch('');
        searchInput.focus();
    });
}

/**
 * FUNGSI: Fetch data search via AJAX dan update table
 * PARAM: {string} query - Keyword pencarian
 * INTERAKSI: Dipanggil via debounce pada input search
 */
function fetchSearch(query) {
    const baseUrl = window.SetorConfig?.routes?.index || '/admin/bank-sampah/setor';
    if (!tableContainer) return;

    // Show loading state
    tableContainer.innerHTML =
        `<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:15px;color:#666;">Mencari...</p></div>`;

    // ✅ GANTI URL FETCH MENGGUNAKAN baseUrl
    fetch(`${baseUrl}?search=${encodeURIComponent(query)}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => updateTable(data.table))
        .catch(err => {
            console.error('Search error:', err);
            // ✅ JANGAN LUPA: Update juga fallback URL di sini
            window.location.href = `${baseUrl}?search=${encodeURIComponent(query)}`;
        });
}

// ===== FILTER FORM =====

/**
 * FUNGSI: Setup AJAX submission untuk filter form (bulan/tahun)
 * INTERAKSI: Dipanggil otomatis saat initSetorPage()
 */
function initFilterForm() {
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const searchQuery = searchInput?.value.trim() || '';
        if (searchQuery) formData.set('search', searchQuery);

        const queryString = new URLSearchParams(formData).toString();
        fetchSearchAjax(queryString);
    });
}

/**
 * FUNGSI: Fetch data filter via AJAX dan update table
 * PARAM: {string} queryString - Query string dari form filter
 * INTERAKSI: Dipanggil saat user submit filter form
 */
function fetchSearchAjax(queryString) {
    const baseUrl = window.SetorConfig?.routes?.index || '/admin/bank-sampah/setor';
    if (!tableContainer) return;

    // Show loading state
    tableContainer.innerHTML =
        `<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:15px;color:#666;">Memfilter...</p></div>`;

    fetch(`${baseUrl}?${queryString}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => updateTable(data.table))
        .catch(err => {
            console.error('Filter error:', err);

            window.location.href = `${baseUrl}?${queryString}`;
        });
}

// ===== TABLE UPDATE =====

/**
 * FUNGSI: Parse HTML response dari AJAX dan update table container
 * PARAM: {string} html - HTML string dari response server
 * INTERAKSI: Dipanggil setelah fetchSearch/fetchSearchAjax sukses
 */
function updateTable(html) {
    if (!html || !tableContainer) return;

    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newContainer = doc.querySelector('.table-container');

    if (newContainer && tableContainer) {
        tableContainer.outerHTML = newContainer.outerHTML;
        // Re-cache elements after DOM update
        tableContainer = document.querySelector('.table-container');
    }

    // Update reset button visibility after table update
    toggleResetButton();
}

// ===== RESET BUTTON =====

/**
 * FUNGSI: Toggle visibility tombol reset berdasarkan filter aktif
 * INTERAKSI: Dipanggil saat input berubah atau table di-update
 */
function toggleResetButton() {
    if (!resetBtn) return;

    // Cek nilai filter
    const bulanSelect = document.querySelector('select[name="bulan"]');
    const tahunSelect = document.querySelector('select[name="tahun"]');
    const bulan = bulanSelect?.value || '';
    const tahun = tahunSelect?.value || '';
    const search = searchInput?.value.trim() || '';

    // Tampilkan Reset jika ada filter aktif
    if (bulan || tahun || search) {
        resetBtn.style.display = 'inline-flex';
    } else {
        resetBtn.style.display = 'none';
    }
}

// ===== MODAL DETAIL (Wrapper untuk fungsi dari Blade) =====
document.addEventListener('DOMContentLoaded', function () {
    initSetorPage();
});

// ===== EXPORT FUNCTIONS (Untuk dipanggil dari Blade jika perlu) =====
// Jika Blade perlu memanggil fungsi JS secara langsung, pastikan fungsi di-declare di global scope
window.SetorJS = {
    fetchSearch: fetchSearch,
    fetchSearchAjax: fetchSearchAjax,
    toggleResetButton: toggleResetButton,
    updateTable: updateTable
};






// MODAL DETAIL SETOR SAMPAH
// ===== STATE =====
let setorModalBody = null;
let setorModalTemplate = null;
let setorModal = null;

// ===== INIT MODAL =====

/**
 * FUNGSI: Inisialisasi referensi elemen modal saat DOM ready
 * INTERAKSI: Auto-trigger via DOMContentLoaded
 */
function initSetorModal() {
    setorModal = document.getElementById('detailModal');
    setorModalBody = document.getElementById('detailModalBody');
    setorModalTemplate = document.getElementById('detailModalTemplate');
}

// ===== FORMAT CURRENCY =====

/**
 * FUNGSI: Format angka menjadi Rupiah (IDR)
 * PARAM: {number|string} angka - Nilai yang akan diformat
 * RETURN: {string} Format Rupiah atau '-' jika invalid
 */
function formatRupiah(angka) {
    if (!angka && angka !== 0) return '-';
    return new Intl.NumberFormat('id-ID').format(angka);
}

// ===== SET VALUE HELPER =====

/**
 * FUNGSI: Set value input dengan fallback aman
 * PARAM: {string} id - ID elemen input
 * PARAM: {string|number|null} value - Nilai yang akan diset
 */
function setModalValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value ?? '-';
}

// ===== OPEN DETAIL MODAL =====

/**
 * FUNGSI: Fetch & tampilkan detail setor sampah via AJAX
 * PARAM: {number} id - ID transaksi setor
 * INTERAKSI: Dipanggil via onclick pada tombol aksi di tabel
 */
function openDetailModal(id) {
    if (!setorModal || !setorModalBody || !setorModalTemplate) {
        initSetorModal();
    }
    
    // Tampilkan loading
    setorModalBody.innerHTML = `<div class="modal-loading"><div class="spinner"></div><p>Memuat data...</p></div>`;
    setorModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Fetch data dari endpoint
    fetch(`/admin/bank-sampah/setor/${id}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Data tidak ditemukan');
        return res.json();
    })
    .then(data => {
        // Render template
        const content = setorModalTemplate.content.cloneNode(true);
        setorModalBody.innerHTML = '';
        setorModalBody.appendChild(content);
        
        // Set values dengan helper
        setModalValue('d_no', data.id_transaksi);
        
        // Nama (fallback masyarakat/pns)
        const namaPengsetor = data.masyarakat?.nama || data.pns?.nama || '-';
        setModalValue('d_nama', namaPengsetor);
        
        // Tipe pengsetor
        const tipePengsetor = data.masyarakat ? 'Masyarakat' : (data.pns ? 'PNS' : '-');
        setModalValue('d_pekerjaan', tipePengsetor);
        
        // Jenis sampah (fallback lengkap)
        const jenisObj = data.jenisSampah || data.jenis_sampah || {};
        const jenisNama = jenisObj.jenis || jenisObj.nama || '-';
        setModalValue('d_jenis', jenisNama);
        
        // Berat
        const berat = data.berat ? parseFloat(data.berat).toFixed(2) + ' Kg' : '-';
        setModalValue('d_berat', berat);
        
        // Harga per kg
        const harga = data.harga_per_kg ? 'Rp ' + formatRupiah(data.harga_per_kg) : '-';
        setModalValue('d_harga', harga);
        
        // Total rupiah
        const total = data.total_rupiah ? 'Rp ' + formatRupiah(data.total_rupiah) : '-';
        setModalValue('d_total', total);
        
        // Petugas
        setModalValue('d_petugas', data.petugas?.nama_lengkap);
        
        // Waktu
        const waktu = data.tanggal_transaksi ? new Date(data.tanggal_transaksi).toLocaleString('id-ID') : '-';
        setModalValue('d_waktu', waktu);
    })
    .catch(err => {
        console.error('❌ Error:', err);
        setorModalBody.innerHTML = `
            <div style="text-align:center;color:#e74c3c;padding:30px 20px;">
                <i class="fas fa-exclamation-triangle" style="font-size:2.5rem;margin-bottom:15px;opacity:0.7;"></i>
                <p style="margin:0;font-weight:500;">Gagal memuat data</p>
                <small style="color:#888;">${err.message}</small>
            </div>`;
    });
}

// ===== CLOSE MODAL =====

/**
 * FUNGSI: Tutup modal detail & restore body scroll
 * INTERAKSI: Dipanggil via onclick pada tombol tutup / backdrop / ESC key
 */
function closeDetailModal() {
    if (setorModal) {
        setorModal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ===== GLOBAL EVENT LISTENERS =====

/**
 * FUNGSI: Setup event listeners global saat DOM ready
 * INTERAKSI: Auto-trigger via DOMContentLoaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initSetorModal();
    
    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && setorModal && setorModal.style.display === 'flex') {
            closeDetailModal();
        }
    });
    
    // Click outside to close (backdrop only)
    document.addEventListener('click', function(e) {
        const modalBox = document.querySelector('.modal-box');
        if (setorModal && modalBox && setorModal.style.display === 'flex') {
            if (!modalBox.contains(e.target) && e.target.classList.contains('modal-backdrop')) {
                closeDetailModal();
            }
        }
    });
});

// ===== EXPORT FUNCTIONS =====
window.SetorModal = {
    openDetailModal: openDetailModal,
    closeDetailModal: closeDetailModal,
    formatRupiah: formatRupiah
};