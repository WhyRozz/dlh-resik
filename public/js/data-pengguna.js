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
