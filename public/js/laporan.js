/**
 * Script untuk halaman Kelola Laporan Admin
 */

// Global variable untuk menyimpan ID laporan yang sedang dibuka
let currentLaporanId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Live Search Functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const nama = row.cells[1]?.textContent.toLowerCase() || '';
                const lokasi = row.cells[2]?.textContent.toLowerCase() || '';
                row.style.display = (nama.includes(query) || lokasi.includes(query)) ? '' : 'none';
            });
        });
    }
});

/**
 * ✅ Tampilkan modal detail laporan
 * @param {HTMLElement} rowElement - Element row yang diklik
 */
function showDetailModal(rowElement) {
    // ✅ Ambil data dari data attributes
    const id = rowElement.dataset.id;
    const nama = rowElement.dataset.nama;
    const lokasi = rowElement.dataset.lokasi;
    const keterangan = rowElement.dataset.keterangan;
    const status = rowElement.dataset.status;
    const balasan = rowElement.dataset.balasan;
    const foto = rowElement.dataset.foto;
    const fotoBalasan = rowElement.dataset.fotoBalasan;
    const tanggal = rowElement.dataset.tanggal;

    currentLaporanId = id;

    // ✅ Isi data ke modal
    document.getElementById('modalId').value = id;
    document.getElementById('modalNama').value = nama;
    document.getElementById('modalLokasi').value = lokasi;
    document.getElementById('modalTanggal').value = tanggal;
    document.getElementById('modalKeterangan').value = keterangan;

    // ✅ Set foto laporan dengan debug
    console.log('📷 Foto URL dari database:', foto);
    const imgElement = document.getElementById('modalFoto');

    if (foto && foto.trim() !== '' && !foto.includes('placeholder')) {
        imgElement.src = foto;

        // Handle error jika gambar tidak ditemukan
        imgElement.onerror = function () {
            console.error('❌ Gagal load foto:', foto);
            console.error('❌ Full path:', this.src);
            this.src = 'https://via.placeholder.com/400x300?text=Gambar+Tidak+Ditemukan';
        };
    } else {
        imgElement.src = 'https://via.placeholder.com/400x300?text=Tidak+Ada+Foto';
    }

    // ✅ Set status radio button
    const statusRadios = document.querySelectorAll('input[name="status"]');
    statusRadios.forEach(radio => {
        radio.checked = (radio.value === status);
    });

    // ✅ Tentukan apakah bisa edit atau read-only
    const isEditable = status === 'Diproses';
    const editSection = document.getElementById('editSection');
    const readOnlySection = document.getElementById('readOnlySection');
    const btnSimpan = document.getElementById('btnSimpan');

    if (isEditable) {
        editSection.style.display = 'block';
        readOnlySection.style.display = 'none';
        btnSimpan.style.display = 'inline-block';

        // Reset form edit
        document.getElementById('modalBalasan').value = balasan || '';
        document.getElementById('fotoBalasan').value = '';
    } else {
        editSection.style.display = 'none';
        readOnlySection.style.display = 'block';
        btnSimpan.style.display = 'none';

        // ✅ Set data read-only
        document.getElementById('modalStatusRead').value = status;

        // ✅ Tampilkan balasan jika ada
        const balasanReadSection = document.getElementById('balasanReadSection');
        if (balasan && balasan.trim() !== '') {
            document.getElementById('modalBalasanRead').value = balasan;
            balasanReadSection.style.display = 'block';
        } else {
            balasanReadSection.style.display = 'none';
        }

        // ✅ Tampilkan foto balasan jika ada
        const fotoBalasanReadSection = document.getElementById('fotoBalasanReadSection');
        if (fotoBalasan && fotoBalasan.trim() !== '') {
            document.getElementById('modalFotoBalasanRead').src = fotoBalasan;
            fotoBalasanReadSection.style.display = 'block';
        } else {
            fotoBalasanReadSection.style.display = 'none';
        }
    }

    // ✅ Tampilkan modal
    const modal = document.getElementById('detailModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scroll
}

/**
 * Tutup modal detail
 */
function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.style.display = 'none';
    document.body.style.overflow = ''; // Restore scroll

    // Reset form
    document.getElementById('fotoBalasan').value = '';
    document.getElementById('modalBalasan').value = '';
    currentLaporanId = null;
}

/**
 * Simpan perubahan status
 */
function saveStatus() {
    if (!currentLaporanId) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan. Silakan coba lagi.',
            confirmButtonColor: '#2e8b57',
            zIndex: 10000
        });
        return;
    }

    const selectedStatus = document.querySelector('input[name="status"]:checked');
    if (!selectedStatus) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih status terlebih dahulu.',
            confirmButtonColor: '#2e8b57',
            zIndex: 10000
        });
        return;
    }

    const status = selectedStatus.value;
    const balasan = document.getElementById('modalBalasan').value.trim();
    const fotoBalasan = document.getElementById('fotoBalasan').files[0];

    // Validasi
    if ((status === 'Diterima' || status === 'Ditolak') && balasan.length > 500) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Balasan terlalu panjang (maksimal 500 karakter).',
            confirmButtonColor: '#2e8b57',
            zIndex: 10000
        });
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Menyimpan...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        zIndex: 10001,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Prepare FormData
    const formData = new FormData();
    formData.append('id', currentLaporanId);
    formData.append('status', status);
    formData.append('balasan', balasan);

    if (fotoBalasan) {
        formData.append('foto_balasan', fotoBalasan);
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Kirim request
    fetch('/admin/laporan/update-status', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            Swal.close();

            if (data.success) {
                // ✅ TUTUP MODAL DENGAN CARA PASTI
                const modal = document.getElementById('detailModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = ''; // Restore scroll
                }

                // Reset form
                document.getElementById('fotoBalasan').value = '';
                document.getElementById('modalBalasan').value = '';
                currentLaporanId = null;

                // ✅ TUNGGU 500MS BARU TAMPILKAN SWEETALERT
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Status berhasil diperbarui!',
                        confirmButtonColor: '#2e8b57',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }, 500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.message || 'Gagal menyimpan data.',
                    confirmButtonColor: '#2e8b57'
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan koneksi.',
                confirmButtonColor: '#2e8b57'
            });
        });
}

// Tutup modal saat klik di luar modal
document.addEventListener('click', function (event) {
    const modal = document.getElementById('detailModal');
    if (modal && event.target === modal) {
        closeDetailModal();
    }
});

// Tutup modal dengan tombol ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('detailModal');
        if (modal && modal.style.display === 'flex') {
            closeDetailModal();
        }
    }
});