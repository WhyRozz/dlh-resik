/**
 * Jenis Sampah - Modal & Form Handler
 */

// Pastikan DOM sudah loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Loaded - Initializing...');
    
    initModals();
});

function initModals() {
    // Close modal when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
}

/**
 * Open modal for add/edit
 */
function openModal(type, id = null, jenis = '', satuan = '', harga = 0, gambar = '') {
    const modal = document.getElementById('formModal');
    const title = document.getElementById('modalTitle');
    const form = document.getElementById('formSampah');
    const method = document.getElementById('formMethod');
    const formId = document.getElementById('formId');

    // Reset form
    form.reset();
    document.getElementById('imagePreview').innerHTML = '<i class="fas fa-image"></i><span>Preview gambar</span>';

    if (type === 'edit') {
        title.textContent = 'Edit Jenis Sampah';
        method.value = 'PUT';
        formId.value = id;
        form.action = `/admin/bank-sampah/jenis-harga/${id}`;

        document.getElementById('jenis').value = jenis;
        document.getElementById('satuan').value = satuan;
        document.getElementById('harga').value = harga;

        if (gambar) {
            document.getElementById('imagePreview').innerHTML = `<img src="${gambar}" alt="Preview">`;
        }
    } else {
        title.textContent = 'Tambah Jenis Sampah';
        method.value = 'POST';
        formId.value = '';
        form.action = '/admin/bank-sampah/jenis-harga';
    }

    modal.classList.add('active');
}

/**
 * Close add/edit modal
 */
function closeModal() {
    document.getElementById('formModal').classList.remove('active');
}

/**
 * Open delete confirmation modal
 */
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Data?',
        text: `Apakah Anda yakin ingin menghapus "${name}"?`,
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
            // Submit form delete
            document.getElementById('deleteForm').action = `/admin/bank-sampah/jenis-sampah/${id}`;
            document.getElementById('deleteForm').submit();
        }
    });
}

/**
 * Close delete confirmation modal
 */
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const table = document.querySelector('.table-container table');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    // Show/hide clear button
    if (searchInput && clearSearch) {
        searchInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                clearSearch.style.display = 'block';
            } else {
                clearSearch.style.display = 'none';
            }
            filterTable(this.value);
        });

        // Clear search
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            clearSearch.style.display = 'none';
            filterTable('');
            searchInput.focus();
        });
    }

    // Filter table function
    function filterTable(searchTerm) {
        const term = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            // Skip empty state row
            if (row.querySelector('.empty-state')) {
                return;
            }
            
            const jenis = row.cells[2]?.textContent.toLowerCase() || '';
            const satuan = row.cells[3]?.textContent.toLowerCase() || '';
            const harga = row.cells[4]?.textContent.toLowerCase() || '';
            
            if (jenis.includes(term) || satuan.includes(term) || harga.includes(term)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});