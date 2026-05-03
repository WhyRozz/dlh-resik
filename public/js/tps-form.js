/**
 * Script untuk Form TPS Admin
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tpsForm');
    const lokasiInput = document.querySelector('textarea[name="lokasi"]');
    const mapsBtn = document.getElementById('openMapsBtn');

    // Koordinat tersimpan dari Blade (jika edit)
    const savedKoordinat = window.tpsFormConfig?.koordinat || null;

    // Open Google Maps button
    if (mapsBtn) {
        mapsBtn.addEventListener('click', function() {
            let url = 'https://www.google.com/maps';

            // Jika ada koordinat tersimpan & valid, buka di lokasi tersebut
            if (savedKoordinat && /^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/.test(savedKoordinat)) {
                url = `https://www.google.com/maps/@${savedKoordinat},18z`;
            } else {
                // Default ke Nganjuk
                url = 'https://www.google.com/maps/@-7.599401,111.900081,11z';
            }

            window.open(url, '_blank');
        });
    }

    // Form validation & submit
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const nama = form.querySelector('[name="nama_tps"]')?.value.trim();
            const lokasi = form.querySelector('[name="lokasi"]')?.value.trim();
            const alamat = form.querySelector('[name="alamat"]')?.value.trim();

            const errors = [];
            if (!nama) errors.push('Nama TPS');
            if (!lokasi) errors.push('Koordinat GPS');
            if (!alamat) errors.push('Alamat Lengkap');

            if (errors.length > 0) {
                showErrorPopup(`Harap isi field berikut: ${errors.join(', ')}.`);
                return;
            }

            // Validasi format koordinat
            if (lokasi && !/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/.test(lokasi)) {
                showErrorPopup('Format koordinat tidak valid. Gunakan: -7.601478,111.943225');
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
 * Close popup when clicking outside
 */
document.addEventListener('click', function(e) {
    const popup = document.getElementById('errorPopup');
    if (popup && e.target === popup) {
        popup.classList.remove('active');
        popup.querySelector('.popup-content')?.classList.remove('show');
    }
});
