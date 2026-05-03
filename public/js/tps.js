/**
 * Script untuk halaman Kelola TPS Admin
 */

document.addEventListener('DOMContentLoaded', function() {
    // Live Search Functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#tpsTableBody tr');

            rows.forEach(row => {
                // Skip empty state row
                if (row.cells.length < 2) return;

                const nama = row.cells[1]?.textContent.toLowerCase() || '';
                const lokasi = row.cells[2]?.textContent.toLowerCase() || '';
                row.style.display = (nama.includes(query) || lokasi.includes(query)) ? '' : 'none';
            });
        });
    }

    // Check for success message from URL params
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const type = urlParams.get('success');
        let message = 'Operasi berhasil.';

        if (type === 'tambah') {
            message = 'Data TPS berhasil ditambahkan.';
        } else if (type === 'edit') {
            message = 'Data TPS berhasil diperbarui.';
        } else if (type === 'hapus') {
            message = 'Data TPS berhasil dihapus.';
        }

        showSuccessPopup(message);

        // Clean URL
        const newUrl = window.location.origin + window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }
});

// Variable to store ID to delete
let idYangAkanDihapus = null;

/**
 * Show delete confirmation popup
 * @param {number} id - TPS ID
 */
function konfirmasiHapus(id) {
    idYangAkanDihapus = id;
    const popup = document.getElementById('confirmPopup');
    if (popup) {
        popup.classList.add('active');
        setTimeout(() => {
            popup.querySelector('.popup-content')?.classList.add('show');
        }, 10);
    }
}

/**
 * Close delete confirmation popup
 */
function closeConfirmPopup() {
    const popup = document.getElementById('confirmPopup');
    if (popup) {
        popup.querySelector('.popup-content')?.classList.remove('show');
        setTimeout(() => {
            popup.classList.remove('active');
        }, 300);
    }
    idYangAkanDihapus = null;
}

/**
 * Delete TPS (submit form)
 */
function hapusTPS() {
    if (idYangAkanDihapus === null) {
        console.error('Tidak ada ID yang dipilih untuk dihapus.');
        closeConfirmPopup();
        return;
    }

    // Create dynamic form for DELETE request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/tps/${idYangAkanDihapus}`;

    // Add CSRF token and method spoofing
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
        <input type="hidden" name="_method" value="DELETE">
    `;

    document.body.appendChild(form);
    form.submit();
}

/**
 * Show success popup
 * @param {string} message - Success message
 */
function showSuccessPopup(message) {
    const popup = document.getElementById('successPopup');
    if (!popup) return;

    const messageEl = document.getElementById('successMessage');
    if (messageEl) messageEl.textContent = message;

    popup.classList.add('active');
    setTimeout(() => {
        popup.querySelector('.popup-content')?.classList.add('show');
    }, 10);
}

/**
 * Close success popup
 */
function closeSuccessPopup() {
    const popup = document.getElementById('successPopup');
    if (popup) {
        popup.querySelector('.popup-content')?.classList.remove('show');
        setTimeout(() => {
            popup.classList.remove('active');
        }, 300);
    }
}

/**
 * Close popup when clicking outside
 */
document.addEventListener('click', function(event) {
    const popups = ['confirmPopup', 'successPopup'];

    popups.forEach(popupId => {
        const popup = document.getElementById(popupId);
        if (popup && event.target === popup) {
            popup.classList.remove('active');
            popup.querySelector('.popup-content')?.classList.remove('show');
        }
    });
});
