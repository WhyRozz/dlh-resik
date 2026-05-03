/**
 * Jenis Sampah - Modal & Form Handler
 */

document.addEventListener('DOMContentLoaded', function() {
    initModals();
    initAlerts();
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

function initAlerts() {
    // Auto hide alert after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.animation = 'slideUp 0.3s ease';
            setTimeout(function() {
                alert.remove();
            }, 300);
        });
    }, 5000);
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
 * Preview image before upload
 */
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Open delete confirmation modal
 */
function confirmDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/bank-sampah/jenis-sampah/${id}`;
    document.getElementById('deleteModal').classList.add('active');
}

/**
 * Close delete confirmation modal
 */
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
