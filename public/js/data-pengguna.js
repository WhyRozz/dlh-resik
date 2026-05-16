/**
 * Data Pengguna - Modal & API Handler
 */

document.addEventListener('DOMContentLoaded', function() {
    initModalEvents();
});

function initModalEvents() {
    const modal = document.getElementById('userModal');

    if (modal) {
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

    function formatTanggal(dateString) {
        if (!dateString) return '-';
        
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        return `${day}-${month}-${year}`;
    }

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

        // Fill modal fields
        document.getElementById('modalNama').textContent = data.nama || '-';
        document.getElementById('modalJenisKelamin').textContent = data.jenis_kelamin || '-';
        document.getElementById('modalEmail').textContent = data.email || '-';
        document.getElementById('modalTelp').textContent = data.no_telepon || '-';
        document.getElementById('modalTglLahir').textContent = formatTanggal(data.tanggal_lahir);
        document.getElementById('modalKodeAnggota').textContent = data.kode_anggota || '-';
        document.getElementById('modalBarcodeId').textContent = data.barcode_id || '-';
        document.getElementById('modalCreated').textContent = formatTanggal(data.created_at);
        document.getElementById('modalPekerjaan').textContent = data.nama_dinas || 'Masyarakat Umum';
        document.getElementById('modalSaldo').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.saldo || 0);

        // Show modal
        document.getElementById('userModal').classList.add('active');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    });
}

/**
 * Close user detail modal
 */
function closeModal() {
    const modal = document.getElementById('userModal');
    if (modal) {
        modal.classList.remove('active');
    }
}






// Live search dengan AJAX + debounce 500ms untuk pencarian real-time
// ===== STATE & ELEMENT REFERENCES =====
let searchTimeout = null;
let tableContainer = null;
let searchInput = null;

// ===== INIT FUNCTIONS =====

/**
 * FUNGSI: Inisialisasi semua event listeners saat DOM ready
 * INTERAKSI: Auto-trigger via DOMContentLoaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initModalEvents();
    initLiveSearch();
});

// ===== MODAL EVENTS =====

/**
 * FUNGSI: Setup event listeners untuk modal (outside click & ESC key)
 * INTERAKSI: Dipanggil otomatis saat DOM ready
 */
function initModalEvents() {
    const modal = document.getElementById('userModal');

    if (modal) {
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

// ===== LIVE SEARCH =====

/**
 * FUNGSI: Setup live search dengan debounce 500ms
 * INTERAKSI: Dipanggil otomatis saat DOM ready
 * PERFORMA: Debounce mencegah multiple request saat user mengetik cepat
 */
function initLiveSearch() {
    searchInput = document.querySelector('.search-input');
    tableContainer = document.querySelector('.table-container');

    if (!searchInput || !tableContainer) return;

    searchInput.addEventListener('input', function(e) {
        const searchValue = e.target.value.trim();
        const filterInput = document.querySelector('input[name="filter"]');
        const currentFilter = filterInput?.value || 'all';
        
        // Clear timeout sebelumnya untuk mencegah multiple request
        clearTimeout(searchTimeout);
        
        // Tunggu 500ms setelah user berhenti mengetik sebelum fetch
        searchTimeout = setTimeout(function() {
            performSearch(searchValue, currentFilter);
        }, 500);
    });
}

/**
 * FUNGSI: Perform search via AJAX dan update tabel
 * PARAM: {string} search - Keyword pencarian
 * PARAM: {string} filter - Kategori filter (all/asn/masyarakat)
 * INTERAKSI: Dipanggil via debounce pada input search
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

// ===== FORMAT HELPER =====

/**
 * FUNGSI: Format tanggal ke format Indonesia DD-MM-YYYY
 * PARAM: {string} dateString - Date string dari database
 * RETURN: {string} Format tanggal DD-MM-YYYY atau '-' jika null
 */
function formatTanggal(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}-${month}-${year}`;
}

// ===== OPEN MODAL =====

/**
 * FUNGSI: Open modal dan fetch user detail via API
 * PARAM: {string|number} userId - User ID
 * PARAM: {string} userType - 'pns' or 'masyarakat'
 * INTERAKSI: Dipanggil via onclick pada tombol detail di tabel
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

        // Fill modal fields
        document.getElementById('modalNama').textContent = data.nama || '-';
        document.getElementById('modalJenisKelamin').textContent = data.jenis_kelamin || '-';
        document.getElementById('modalEmail').textContent = data.email || '-';
        document.getElementById('modalTelp').textContent = data.no_telepon || '-';
        document.getElementById('modalTglLahir').textContent = formatTanggal(data.tanggal_lahir);
        document.getElementById('modalKodeAnggota').textContent = data.kode_anggota || '-';
        document.getElementById('modalBarcodeId').textContent = data.barcode_id || '-';
        document.getElementById('modalCreated').textContent = formatTanggal(data.created_at);
        document.getElementById('modalPekerjaan').textContent = data.nama_dinas || 'Masyarakat Umum';
        document.getElementById('modalSaldo').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.saldo || 0);

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
 * FUNGSI: Close user detail modal
 * INTERAKSI: Dipanggil via onclick pada tombol close / outside click / ESC key
 */
function closeModal() {
    const modal = document.getElementById('userModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

// ===== EXPORT FUNCTIONS =====
window.DataPenggunaJS = {
    openModal: openModal,
    closeModal: closeModal,
    formatTanggal: formatTanggal,
    performSearch: performSearch
};