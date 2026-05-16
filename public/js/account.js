/**
 * Script untuk halaman Kelola Akun Admin
 */

document.addEventListener('DOMContentLoaded', function () {
    // State management
    window.accountState = {
        currentAction: '',
        currentIdAdmin: null,
        currentEmail: '',
        isEditing: false
    };

    // Toggle password visibility
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIconImg');

    if (togglePasswordBtn && passwordInput && eyeIcon) {
        togglePasswordBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                // Jika mode edit, fetch password raw dari server
                if (window.accountState.isEditing) {
                    const idAdmin = document.getElementById('formIdAdmin')?.value;
                    if (idAdmin) {
                        fetchPasswordRaw(idAdmin)
                            .then(password => {
                                passwordInput.type = 'text';
                                passwordInput.value = password;
                                eyeIcon.src = '/assets/show.png';
                            })
                            .catch(() => {
                                alert('Gagal memuat password.');
                            });
                    }
                } else {
                    passwordInput.type = 'text';
                    eyeIcon.src = '/assets/show.png';
                }
            } else {
                passwordInput.type = 'password';
                eyeIcon.src = '/assets/hide.png';
            }
        });
    }

    // Form submission validation
    const accountForm = document.getElementById('accountForm');
    if (accountForm) {
        accountForm.addEventListener('submit', function (e) {
            const password = passwordInput?.value.trim();
            if (!window.accountState.isEditing && !password) {
                e.preventDefault();
                alert('Password wajib diisi untuk akun baru.');
                passwordInput?.focus();
            }
        });
    }

    // Modal close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            });
        }
    });

    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
});

/**
 * Fetch decrypted password from server (edit mode only)
 */
async function fetchPasswordRaw(idAdmin) {
    const response = await fetch('/admin/akun/ajax/get-password-raw', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: `id_admin=${encodeURIComponent(idAdmin)}`
    });

    const data = await response.json();

    if (data.status === 'success') {
        return data.password;
    }
    throw new Error(data.message || 'Failed to fetch password');
}

/**
 * Show add account form
 */
function showAddForm() {
    // Check account limit (server-side validation will also check)
    const totalAccounts = document.querySelectorAll('.account-card').length;
    if (totalAccounts >= 3) {
        showModal('limitModal');
        return;
    }

    resetForm();
    document.getElementById('formTitle').textContent = 'Tambah Akun Baru';
    document.getElementById('formSection').style.display = 'block';
    document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
    window.accountState.isEditing = false;
}

/**
 * Create default account (pre-filled form)
 */
function createDefaultAccount() {
    resetForm();
    document.getElementById('formTitle').textContent = 'Buat Akun Utama';
    document.getElementById('email').value = 'simpelsi2025@gmail.com';
    document.getElementById('password').value = 'Admin123';
    document.getElementById('formSection').style.display = 'block';
    document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
    window.accountState.isEditing = false;
}

/**
 * Reset form to initial state
 */
function resetForm() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const idInput = document.getElementById('formIdAdmin');
    const eyeIcon = document.getElementById('eyeIconImg');

    if (emailInput) emailInput.value = '';
    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.type = 'password';
    }
    if (idInput) idInput.value = '';
    if (eyeIcon) eyeIcon.src = '/assets/hide.png';

    window.accountState.isEditing = false;
}

/**
 * Request OTP for sensitive action (edit/delete)
 */
function requestOTPForAction(action, idAdmin, email) {
    window.accountState.currentAction = action;
    window.accountState.currentIdAdmin = idAdmin;
    window.accountState.currentEmail = email;

    // Update modal content
    document.getElementById('targetEmailDisplay').textContent = email;
    document.getElementById('otpTargetEmail').textContent = email;

    let title = 'Verifikasi ';
    if (action === 'edit') title += 'Edit Akun';
    else if (action === 'delete') title += 'Hapus Akun';
    document.getElementById('otpModalTitle').textContent = title;

    // Clear previous status
    document.getElementById('otpRequestStatus').innerHTML = '';

    showModal('otpRequestModal');
}

/**
 * Send OTP to target email
 */
async function sendOTPToTarget() {
    const email = window.accountState.currentEmail;
    const statusDiv = document.getElementById('otpRequestStatus');

    try {
        const response = await fetch('/admin/akun/request-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        });

        const data = await response.json();

        if (data.status === 'success' || data.status === 'success_dev') {
            let msg = `<div style="color:#20A726;">✅ OTP dikirim ke ${email}</div>`;
            if (data.status === 'success_dev') {
                msg = `<div style="background:#e6f7e6; padding:8px; border-radius:4px; color:#095E0D;">[DEV] Gunakan kode: <strong>${data.otp}</strong></div>`;
            }
            statusDiv.innerHTML = msg;

            // Auto-proceed to verify modal after short delay
            setTimeout(() => {
                hideModal('otpRequestModal');
                showModal('otpVerifyModal');

                // Pre-fill OTP in dev mode
                if (data.otp) {
                    document.getElementById('otpInput').value = data.otp;
                }
                document.getElementById('otpInput').focus();
            }, 800);
        } else {
            statusDiv.innerHTML = `<div class="alert-error">${data.message}</div>`;
        }
    } catch (error) {
        console.error('OTP request failed:', error);
        statusDiv.innerHTML = '<div class="alert-error">❌ Gagal kirim OTP.</div>';
    }
}

/**
 * Verify OTP code
 */
async function verifyOTP() {
    const otpInput = document.getElementById('otpInput');
    const otp = otpInput?.value.trim();
    const statusDiv = document.getElementById('otpVerifyStatus');

    // Validate input
    if (!otp || otp.length !== 4 || !/^\d+$/.test(otp)) {
        statusDiv.innerHTML = '<div class="alert-error">OTP harus 4 digit angka.</div>';
        return;
    }

    try {
        const response = await fetch('/admin/akun/verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: window.accountState.currentEmail,
                otp: otp
            })
        });

        const data = await response.json();

        if (data.status === 'success') {
            statusDiv.innerHTML = `<div style="color:#20A726;">✅ OTP valid. Memproses...</div>`;

            setTimeout(() => {
                hideModal('otpVerifyModal');
                executeAction();
            }, 600);
        } else {
            statusDiv.innerHTML = `<div class="alert-error">${data.message}</div>`;
        }
    } catch (error) {
        console.error('OTP verify failed:', error);
        statusDiv.innerHTML = '<div class="alert-error">❌ Kesalahan jaringan.</div>';
    }
}

/**
 * Execute the verified action (edit or delete)
 */
function executeAction() {
    const { currentAction, currentIdAdmin, currentEmail } = window.accountState;

    if (currentAction === 'edit') {
        // Load edit form with existing data
        document.getElementById('formIdAdmin').value = currentIdAdmin;
        document.getElementById('email').value = currentEmail;
        document.getElementById('formTitle').textContent = 'Edit Akun';

        // Fetch placeholder password for display
        fetch('/admin/akun/ajax/get-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: `id_admin=${encodeURIComponent(currentIdAdmin)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('password').value = data.password;
                    window.accountState.isEditing = true;
                } else {
                    alert('⚠️ ' + (data.message || 'Gagal memuat password.'));
                    document.getElementById('password').value = '';
                    window.accountState.isEditing = false;
                }
            })
            .catch(() => {
                alert('⚠️ Gagal menghubungi server.');
                document.getElementById('password').value = '';
                window.accountState.isEditing = false;
            })
            .finally(() => {
                document.getElementById('formSection').style.display = 'block';
                document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
            });

    } else if (currentAction === 'delete') {
        // Confirm and submit delete form
        if (confirm(`Yakin hapus akun ${currentEmail}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/akun/${currentIdAdmin}`;

            // Add CSRF token and method spoofing
            form.innerHTML = `
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                <input type="hidden" name="_method" value="DELETE">
            `;

            document.body.appendChild(form);
            form.submit();
        }
    }
}

/**
 * Show modal by ID
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

/**
 * Hide modal by ID
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Close modal (alias for hideModal)
 */
function closeModal(modalId) {
    hideModal(modalId);
}




/**
 * ========================================
 * SCRIPT HALAMAN KELOLA AKUN ADMIN
 * File: public/js/account.js
 * Project: SIMPELSI - DLH Resik
 * ========================================
 * 
 * FUNGSI UTAMA:
 * - Toggle visibility password dengan fetch decrypted password dari server
 * - Validasi form client-side untuk UX enhancement
 * - OTP flow untuk edit/delete akun admin (keamanan 2FA)
 * - CRUD petugas lapangan dengan SweetAlert2 confirmation
 * - Search filter client-side untuk akun & petugas
 * - Modal handling (ESC key, outside click)
 * 
 * KEAMANAN:
 * - CSRF token dipassing via window.AccountConfig dari Blade
 * - Semua input divalidasi di server-side (Controller)
 * - Password di-fetch via endpoint terenkripsi, tidak pernah dikirim plain
 * - Action sensitif (edit/delete) wajib melalui OTP verification
 * 
 * INTERAKSI:
 * - Dipanggil dari index.blade.php via <script src="{{ asset('js/account.js') }}">
 * - Fungsi dipanggil via onclick attribute pada elemen HTML
 */

// ===== STATE GLOBAL =====
// FUNGSI: Menyimpan state action, ID, dan email saat ini (shared antar fungsi)
let currentAction = null;
let currentId = null;
let currentEmail = null;

// ===== MODAL HELPERS =====

/**
 * FUNGSI: Menampilkan modal dengan menambahkan display flex
 * PARAM: {string} modalId - ID elemen modal yang akan ditampilkan
 * INTERAKSI: Dipanggil saat user klik tombol yang memicu modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'flex';
}

/**
 * FUNGSI: Menyembunyikan modal dan reset form OTP jika ada
 * PARAM: {string} modalId - ID elemen modal yang akan disembunyikan
 * INTERAKSI: Dipanggil saat user klik tombol batal atau verifikasi selesai
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
    if (modalId === 'otpVerifyModal') {
        const otpInput = document.getElementById('otpInput');
        const otpStatus = document.getElementById('otpVerifyStatus');
        if (otpInput) otpInput.value = '';
        if (otpStatus) otpStatus.innerHTML = '';
    }
}

// FUNGSI: Close modal when clicking outside content (UX pattern)
window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// ===== ADMIN FORM =====

/**
 * FUNGSI: Menampilkan section form admin yang tersembunyi
 * INTERAKSI: Dipanggil via onclick pada tombol "Buat Akun Utama" / "Tambah Akun"
 */
function showAdminForm() {
    const section = document.getElementById('adminFormSection');
    if (section) section.style.display = 'block';
}

// ===== OTP FLOW =====

/**
 * FUNGSI: Request OTP untuk action sensitif (edit/delete akun admin)
 * PARAM: {string} action - 'edit_admin' atau 'delete_admin'
 * PARAM: {number} id - ID admin target
 * PARAM: {string} email - Email admin target untuk display di modal
 * INTERAKSI: Dipanggil via onclick pada tombol Edit di card akun
 */
function requestOTPForAction(action, id, email) {
    currentAction = action;
    currentId = id;
    currentEmail = email;
    const display = document.getElementById('targetEmailDisplay');
    if (display) display.textContent = email;
    openModal('otpRequestModal');
}

/**
 * FUNGSI: Kirim request OTP ke backend via AJAX
 * KEAMANAN: CSRF token dari window.AccountConfig, endpoint harus validasi email & rate limit
 * INTERAKSI: Dipanggil saat user klik "Kirim Kode OTP" di modal
 */
async function sendOTPToTarget() {
    try {
        const response = await fetch(window.AccountConfig.routes.requestOtp, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.AccountConfig.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: currentEmail })
        });

        const data = await response.json();

        if (data.status === 'success' || data.status === 'success_dev') {
            const targetEmail = document.getElementById('otpTargetEmail');
            if (targetEmail) targetEmail.textContent = currentEmail;
            closeModal('otpRequestModal');
            openModal('otpVerifyModal');
            if (data.otp) alert('Dev Mode - OTP: ' + data.otp);
        } else {
            alert('Gagal kirim OTP');
        }
    } catch (e) {
        console.error(e);
        alert('Error');
    }
}

/**
 * FUNGSI: Verifikasi kode OTP yang dimasukkan user
 * KEAMANAN: OTP hanya valid sekali pakai & expired setelah 5 menit
 * INTERAKSI: Dipanggil saat user klik "Verifikasi" di modal OTP
 */
async function verifyOTP() {
    const otpInput = document.getElementById('otpInput');
    const otp = otpInput ? otpInput.value : '';

    if (!otp || otp.length !== 4) {
        const status = document.getElementById('otpVerifyStatus');
        if (status) {
            status.innerHTML = '<div class="alert alert-error">OTP harus 4 digit!</div>';
        }
        return;
    }

    try {
        const response = await fetch(window.AccountConfig.routes.verifyOtp, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.AccountConfig.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: currentEmail, otp: otp })
        });

        const data = await response.json();

        if (data.status === 'success') {
            closeModal('otpVerifyModal');
            executeAdminAction();
        } else {
            const status = document.getElementById('otpVerifyStatus');
            if (status) {
                status.innerHTML = '<div class="alert alert-error">OTP tidak valid!</div>';
            }
        }
    } catch (e) {
        console.error(e);
        alert('Error');
    }
}

/**
 * FUNGSI: Eksekusi action admin (edit atau delete) setelah OTP terverifikasi
 * INTERAKSI: Dipanggil otomatis setelah verifyOTP() sukses
 */
function executeAdminAction() {
    if (currentAction === 'edit_admin') {
        loadAdminForEdit(currentId);
    } else if (currentAction === 'delete_admin') {
        deleteAdmin(currentId);
    }
}

/**
 * FUNGSI: Load data admin untuk diedit dan isi form dengan data existing
 * PARAM: {number} id - ID admin yang akan diedit
 * INTERAKSI: Dipanggil setelah OTP verifikasi sukses untuk action edit
 */
async function loadAdminForEdit(id) {
    showAdminForm();
    const formId = document.getElementById('adminFormId');
    if (formId) formId.value = id;

    const formTitle = document.getElementById('adminFormTitle');
    if (formTitle) formTitle.textContent = 'Edit Akun Admin';

    const form = document.getElementById('adminForm');
    if (form) {
        form.action = `/admin/akun/${id}`;
        let methodField = form.querySelector('input[name="_method"]');
        if (!methodField) {
            methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PUT';
            form.appendChild(methodField);
        } else {
            methodField.value = 'PUT';
        }
    }

    try {
        const response = await fetch(`/admin/akun/${id}`);
        const data = await response.json();
        const emailInput = document.getElementById('adminEmail');
        const passInput = document.getElementById('adminPassword');
        if (emailInput) emailInput.value = data.email;
        if (passInput) {
            passInput.value = '';
            passInput.placeholder = 'Kosongkan jika tidak ingin mengubah';
        }
    } catch (e) {
        console.error('Load admin error:', e);
    }
}

/**
 * FUNGSI: Hapus akun admin dengan konfirmasi dan submit form DELETE
 * PARAM: {number} id - ID admin yang akan dihapus
 * INTERAKSI: Dipanggil setelah OTP verifikasi sukses untuk action delete
 */
function deleteAdmin(id) {
    if (!confirm('Yakin hapus akun ini?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/akun/${id}`;
    form.innerHTML = `<input type="hidden" name="_token" value="${window.AccountConfig.csrfToken}"><input type="hidden" name="_method" value="DELETE">`;
    document.body.appendChild(form);
    form.submit();
}

// ===== PETUGAS CRUD =====

/**
 * FUNGSI: Konfirmasi hapus petugas dengan SweetAlert2 dan AJAX DELETE request
 * PARAM: {number} id - ID petugas yang akan dihapus
 * INTERAKSI: Dipanggil via onclick pada tombol hapus di tabel petugas
 */
function confirmDelete(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data petugas akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/petugas/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.AccountConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#20A726'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                });
        }
    });
}

/**
 * FUNGSI: Menutup modal konfirmasi hapus
 * INTERAKSI: Dipanggil saat user klik tombol "Batal" di modal
 */
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'none';
}

/**
 * FUNGSI: Membuka modal petugas untuk mode tambah atau edit
 * PARAM: {string} mode - 'add' atau 'edit'
 * PARAM: {object} data - Data petugas untuk mode edit (opsional)
 * INTERAKSI: Dipanggil via onclick pada tombol "+ Tambah Akun" atau tombol Edit di tabel
 */
function openPetugasModal(mode, data = null) {
    const modal = document.getElementById('modalPetugas');
    const title = document.getElementById('modalPetugasTitle');
    const passHint = document.getElementById('passHint');
    const btnSimpan = document.getElementById('btnSimpanPetugas');

    if (!modal) {
        alert('⚠️ Modal tidak ditemukan!');
        console.error('Modal #modalPetugas not found');
        return;
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    if (mode === 'edit' && data) {
        if (title) title.textContent = 'Edit Akun Petugas';
        if (btnSimpan) btnSimpan.textContent = 'Update';
        if (passHint) passHint.textContent = '(Kosongkan jika tidak ingin mengubah)';

        document.getElementById('petugasId')?.setAttribute('value', data.id || '');
        document.getElementById('namaLengkap')?.setAttribute('value', data.nama || '');
        document.getElementById('emailPetugas')?.setAttribute('value', data.email || '');
        document.getElementById('noTelepon')?.setAttribute('value', data.telpon || '');
        
        const levelSelect = document.getElementById('levelPetugas');
        if (levelSelect && data.level) {
            levelSelect.value = data.level;
        }

        const passwordPetugas = document.getElementById('passwordPetugas');
        if (passwordPetugas) {
            passwordPetugas.value = '';
            passwordPetugas.required = false;
        }
    } else {
        if (title) title.textContent = 'Tambah Akun Petugas';
        if (btnSimpan) btnSimpan.textContent = 'Simpan';
        if (passHint) passHint.textContent = '';

        document.getElementById('formPetugas')?.reset();
        document.getElementById('petugasId')?.setAttribute('value', '');

        const passwordPetugas = document.getElementById('passwordPetugas');
        if (passwordPetugas) passwordPetugas.required = true;
    }
}

/**
 * FUNGSI: Menutup modal petugas dan reset form
 * INTERAKSI: Dipanggil saat user klik tombol batal atau klik di luar modal
 */
function closeModalPetugas() {
    const modal = document.getElementById('modalPetugas');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('formPetugas')?.reset();
}

// ===== SEARCH FUNCTION =====

/**
 * FUNGSI: Filter akun & petugas client-side berdasarkan keyword (tanpa reload)
 * INTERAKSI: Dipanggil via oninput pada input search #searchAkun
 * PERFORMA: Loop sederhana, cocok untuk data < 1000 record
 */
function initAccountSearch() {
    const searchInput = document.getElementById('searchAkun');
    if (!searchInput) return;

    searchInput.addEventListener('input', function (e) {
        const keyword = e.target.value.toLowerCase().trim();

        // Filter Cards (Akun Utama & Kedua)
        document.querySelectorAll('.account-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(keyword) ? '' : 'none';
        });

        // Filter Table Rows (Petugas)
        document.querySelectorAll('.petugas-table-container tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });
    });
}

// ===== INIT ON DOM READY =====

/**
 * FUNGSI: Setup event listeners dan init logic saat DOM ready
 * INTERAKSI: Auto-trigger saat halaman selesai load
 */
document.addEventListener('DOMContentLoaded', function () {
    // Modal Petugas Event Listeners
    const btnClose = document.getElementById('btnClosePetugasModal');
    const btnBatal = document.getElementById('btnBatalPetugas');
    const modal = document.getElementById('modalPetugas');

    btnClose?.addEventListener('click', closeModalPetugas);
    btnBatal?.addEventListener('click', closeModalPetugas);

    modal?.addEventListener('click', (e) => {
        if (e.target.id === 'modalPetugas') closeModalPetugas();
    });

    // Close with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal?.classList.contains('active')) {
            closeModalPetugas();
        }
    });

    // Form Submit Handler for Petugas
    const form = document.getElementById('formPetugas');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const petugasId = document.getElementById('petugasId');
            const isEdit = petugasId && petugasId.value !== '';
            const btnSimpan = document.getElementById('btnSimpanPetugas');

            if (btnSimpan) {
                btnSimpan.disabled = true;
                btnSimpan.textContent = isEdit ? 'Menyimpan...' : 'Menambahkan...';
            }

            const formData = new FormData(form);
            let url = window.AccountConfig?.routes?.petugasStore || '{{ url("admin/petugas") }}';

            if (isEdit) {
                url = `${url}/${encodeURIComponent(petugasId.value)}`;
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': window.AccountConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    }
                });

                const contentType = response.headers.get("content-type");

                if (contentType && contentType.includes("application/json")) {
                    const result = await response.json();

                    if (result.success || response.ok) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: isEdit ? 'Data petugas telah diperbarui.' : 'Petugas baru berhasil ditambahkan.',
                            icon: 'success',
                            confirmButtonColor: '#20A726',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            closeModalPetugas();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: result.message || 'Terjadi kesalahan',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } else {
                    const errorText = await response.text();
                    console.error('Server Error:', errorText);
                    alert('❌ Gagal: Server error. Cek Console (F12).');
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                alert('❌ Network error: ' + error.message);
            } finally {
                if (btnSimpan) {
                    btnSimpan.disabled = false;
                    btnSimpan.textContent = isEdit ? 'Update' : 'Simpan';
                }
            }
        });
    }

    // Init search
    initAccountSearch();
});