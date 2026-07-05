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

// FUNGSI: Cache DOM elements untuk performance (hindari query berulang)
const form = document.getElementById('artikelForm');
const uploadArea = document.getElementById('uploadArea');
const fotoInput = document.getElementById('fotoInput');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const uploadPreview = document.getElementById('uploadPreview');
const previewImage = document.getElementById('previewImage');
const submitBtn = document.getElementById('submitBtn');

// ===== UPLOAD: CLICK TO TRIGGER FILE INPUT =====

/**
 * FUNGSI: Trigger file input ketika area upload diklik (UX enhancement)
 * INTERAKSI: User klik area placeholder → file picker terbuka
 * KEAMANAN: Hanya trigger jika klik pada area yang valid (bukan preview/remove button)
 */
if (uploadArea && fotoInput) {
    uploadArea.addEventListener('click', function(e) {
        // FUNGSI: Cek target klik bukan pada preview atau tombol remove
        if (e.target === uploadArea || 
            e.target.closest('.upload-placeholder') || 
            e.target.closest('.upload-icon') || 
            e.target.closest('.upload-text')) {
            fotoInput.click();
        }
    });
}

// ===== UPLOAD: FILE SELECTION & VALIDATION =====

/**
 * FUNGSI: Handle file selection dengan validasi tipe & ukuran
 * KEAMANAN: Validasi client-side untuk UX, tetap divalidasi server-side
 * INTERAKSI: Dipanggil otomatis saat user pilih file
 */
if (fotoInput) {
    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // FUNGSI: Validasi ukuran file maksimal 10MB (10 * 1024 * 1024 bytes)
        if (file.size > 10 * 1024 * 1024) {
            showError('Ukuran gambar maksimal 10MB');
            fotoInput.value = '';
            return;
        }
        
        // FUNGSI: Validasi tipe file hanya JPG, PNG, GIF (mencegah upload file berbahaya)
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            showError('Format gambar harus JPG, JPEG, atau PNG');
            fotoInput.value = '';
            return;
        }
        
        // FUNGSI: Preview gambar menggunakan FileReader API (tanpa upload ke server dulu)
        const reader = new FileReader();
        reader.onload = function(event) {
            showPreview(event.target.result);
        };
        reader.readAsDataURL(file);
    });
}

// ===== UPLOAD: DRAG & DROP SUPPORT =====

/**
 * FUNGSI: Enable drag & drop untuk upload gambar (UX enhancement)
 * INTERAKSI: User drag file gambar ke area upload → file otomatis terpilih
 */
if (uploadArea) {
    // FUNGSI: Prevent default behavior untuk event drag
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // FUNGSI: Tambahkan visual feedback saat file di-drag over area
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

    // FUNGSI: Handle file drop
    uploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files[0] && fotoInput) {
            fotoInput.files = files;
            fotoInput.dispatchEvent(new Event('change'));
        }
    }
}

// ===== UPLOAD: PREVIEW MANAGEMENT =====

/**
 * FUNGSI: Menampilkan preview gambar dan menyembunyikan placeholder
 * PARAM: {string} src - Data URL atau URL gambar untuk preview
 * INTERAKSI: Dipanggil setelah validasi file sukses atau saat mode edit
 */
function showPreview(src) {
    if (uploadPlaceholder) uploadPlaceholder.style.display = 'none';
    if (uploadPreview) uploadPreview.classList.add('show');
    if (previewImage && src) previewImage.src = src;
}

/**
 * FUNGSI: Menghapus gambar yang dipilih dan reset ke state placeholder
 * INTERAKSI: Dipanggil via onclick pada tombol "×" di preview
 */
function removeImage() {
    if (fotoInput) fotoInput.value = '';
    if (uploadPlaceholder) uploadPlaceholder.style.display = 'flex';
    if (uploadPreview) uploadPreview.classList.remove('show');
    if (previewImage) previewImage.src = '';
}

// ===== ERROR MODAL MANAGEMENT =====

/**
 * FUNGSI: Menampilkan modal error dengan pesan dari server validation
 * PARAM: {string} message - Pesan error yang akan ditampilkan
 * INTERAKSI: Dipanggil saat ada validation error dari Laravel atau client-side
 */
function showError(message) {
    const errorEl = document.getElementById('errorMessage');
    if (errorEl) errorEl.textContent = message;
    const modal = document.getElementById('errorModal');
    if (modal) modal.classList.add('show');
}

/**
 * FUNGSI: Menyembunyikan modal error
 * INTERAKSI: Dipanggil saat user klik tombol "Tutup"
 */
function hideErrorModal() {
    const modal = document.getElementById('errorModal');
    if (modal) modal.classList.remove('show');
}

// ===== FORM SUBMIT: LOADING STATE =====

/**
 * FUNGSI: Handle form submit dengan UX feedback (disable button, loading text)
 * KEAMANAN: Validasi client-side sebagai UX enhancement, validasi utama di server
 * INTERAKSI: Dipanggil otomatis saat user submit form
 */
if (form) {
    form.addEventListener('submit', function(e) {
        // FUNGSI: Disable tombol submit untuk mencegah double-submit
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
        }
        // FUNGSI: Form akan submit secara native ke Laravel (tidak perlu preventDefault)
    });
}

// ===== EVENT LISTENERS: MODAL CLOSE & AUTO-INIT =====

/**
 * FUNGSI: Setup event listeners dan auto-init logic saat DOM ready
 * INTERAKSI: Auto-trigger saat halaman selesai load
 */
document.addEventListener('DOMContentLoaded', function() {
    // FUNGSI: Close error modal when clicking outside content
    const errorModal = document.getElementById('errorModal');
    if (errorModal) {
        errorModal.addEventListener('click', function(e) {
            if (e.target === this) hideErrorModal();
        });
    }
});