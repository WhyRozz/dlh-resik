/**
 * Script untuk halaman Kelola Akun Admin
 */

document.addEventListener('DOMContentLoaded', function() {
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
        togglePasswordBtn.addEventListener('click', function() {
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
        accountForm.addEventListener('submit', function(e) {
            const password = passwordInput?.value.trim();
            if (!window.accountState.isEditing && !password) {
                e.preventDefault();
                alert('Password wajib diisi untuk akun baru.');
                passwordInput?.focus();
            }
        });
    }

    // Modal close on ESC
    document.addEventListener('keydown', function(e) {
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
        modal.addEventListener('click', function(e) {
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
