/**
 * Script untuk Form Artikel Admin
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('artikelForm');
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fotoInput');
    const preview = document.getElementById('uploadPreview');
    const previewImg = preview?.querySelector('img');

    // Click upload area to trigger file input
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', function(e) {
            if (e.target === uploadArea || e.target.closest('.upload-icon') || e.target.closest('.upload-text')) {
                fileInput.click();
            }
        });
    }

    // Preview image on file select
    if (fileInput && preview) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showErrorPopup('Format gambar tidak didukung! Gunakan: JPG, JPEG, PNG, GIF.');
                    fileInput.value = '';
                    return;
                }

                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showErrorPopup('Ukuran file terlalu besar! Maksimal 2MB.');
                    fileInput.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (!previewImg) {
                        const img = document.createElement('img');
                        img.alt = 'Preview';
                        preview.appendChild(img);
                    }
                    preview.querySelector('img').src = event.target.result;
                    preview.classList.add('show');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Drag & drop support
    if (uploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            }, false);
        });

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files[0] && fileInput) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        }
    }

    // Form validation & submit
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const judul = form.querySelector('input[name="judul"]')?.value.trim();
            const tanggal = form.querySelector('input[name="tanggal"]')?.value.trim();
            const deskripsi = form.querySelector('textarea[name="deskripsi"]')?.value.trim();

            const errors = [];
            if (!judul) errors.push('Judul Artikel');
            if (!tanggal) errors.push('Tanggal Publikasi');
            if (!deskripsi) errors.push('Deskripsi Artikel');

            if (errors.length > 0) {
                showErrorPopup(`Harap isi field berikut: ${errors.join(', ')}.`);
                return;
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Menyimpan...';
            }

            // Submit form
            form.submit();
        });
    }

    // Remove uploaded image preview
    const removeBtn = preview?.querySelector('.remove-btn');
    if (removeBtn && fileInput) {
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.value = '';
            preview.classList.remove('show');
            if (previewImg) {
                previewImg.src = '';
            }
        });
    }
});

/**
 * Show error popup
 * @param {string} message - Error message
 */
function showErrorPopup(message) {
    const popup = document.getElementById('errorPopup');
    if (!popup) return;

    const messageEl = document.getElementById('errorMessage');
    if (messageEl) messageEl.textContent = message;

    popup.classList.add('active');
    setTimeout(() => {
        popup.querySelector('.popup-content')?.classList.add('show');
    }, 10);
}

/**
 * Close error popup
 */
function closeErrorPopup() {
    const popup = document.getElementById('errorPopup');
    if (!popup) return;

    popup.querySelector('.popup-content')?.classList.remove('show');
    setTimeout(() => {
        popup.classList.remove('active');
    }, 300);
}

/**
 * Show success popup (for redirect after save)
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
 * Close success popup and redirect
 */
function closeSuccessPopup(redirectUrl) {
    const popup = document.getElementById('successPopup');
    if (!popup) return;

    popup.querySelector('.popup-content')?.classList.remove('show');
    setTimeout(() => {
        popup.classList.remove('active');
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    }, 300);
}

// Close popup on outside click
document.addEventListener('click', function(e) {
    ['errorPopup', 'successPopup'].forEach(popupId => {
        const popup = document.getElementById(popupId);
        if (popup && e.target === popup) {
            popup.classList.remove('active');
            popup.querySelector('.popup-content')?.classList.remove('show');
        }
    });
});
