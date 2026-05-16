/**
 * Jenis Sampah - Modal & Form Handler
 */

document.addEventListener('DOMContentLoaded', function() {
    initModals();
    initAlerts();
    initImageUpload();
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


function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const uploadText = input.parentElement.querySelector('p');
    
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = '';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Preview';
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'contain';
            
            preview.style.display = 'block';
            preview.appendChild(img);
            
            if (uploadText) {
                uploadText.textContent = 'Klik untuk ganti foto';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}


/**
 * Close add/edit modal
 */
function closeModal() {
    document.getElementById('formModal').classList.remove('active');
}

function initImageUpload() {
    const imageUpload = document.querySelector('.image-upload');
    const fileInput = document.getElementById('gambar');
    
    if (imageUpload && fileInput) {
        imageUpload.addEventListener('click', function(e) {
            if (e.target.closest('.image-preview img')) {
                return;
            }
            e.stopPropagation();
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function(e) {
            e.stopPropagation();
            previewImage(this);
        });
        
        fileInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });
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


