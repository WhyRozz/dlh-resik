// ===== STATE GLOBAL (Unified) =====
if (!window.accountState) {
    window.accountState = {
        currentAction: '',
        currentIdAdmin: null,
        currentEmail: '',
        isEditing: false
    };
}

// ===== SETUP FORM ACTION =====
function setupAccountForm() {
    const formSection = document.getElementById('formSection');
    const accountForm = document.getElementById('accountForm');
    const idInput = document.getElementById('formIdAdmin');
    
    if (!formSection || !accountForm) {
        console.error('❌ formSection atau accountForm tidak ada!');
        return;
    }
    
    console.log('🔧 Setup form action listener...');
    
    accountForm.addEventListener('submit', function(e) {
        // Ambil ID dari berbagai sumber
        let idAdmin = idInput?.value?.trim();
        
        console.log('📮 Form submit - Initial ID:', idAdmin);
        console.log('📮 window.accountState:', window.accountState);
        
        // Fallback 1: Dari input hidden
        if (!idAdmin || idAdmin === '') {
            idAdmin = window.accountState?.currentIdAdmin;
            console.log('🔄 ID diambil dari window.accountState:', idAdmin);
        }
        
        // Fallback 2: Dari data attribute form
        if (!idAdmin || idAdmin === '') {
            idAdmin = accountForm.dataset.adminId;
            console.log('🔄 ID diambil dari form dataset:', idAdmin);
        }
        
        const email = document.getElementById('email')?.value?.trim();
        
        console.log('📮 Final values:', { idAdmin, email });
        
        if (idAdmin && email) {
            this.action = "/admin/akun/" + idAdmin;
            this.method = "POST";
            
            let methodInput = this.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                this.appendChild(methodInput);
            }
            methodInput.value = 'PUT';
            
            let csrfInput = this.querySelector('input[name="_token"]');
            if (!csrfInput) {
                csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = window.AccountConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
                this.appendChild(csrfInput);
            }
            
            console.log('✅ Form diset ke PUT /admin/akun/' + idAdmin);
        } else {
            e.preventDefault();
            console.error('❌ Validasi gagal:', { idAdmin, email });
            
            let errorMsg = 'Email dan ID Admin wajib diisi!\n\n';
            errorMsg += 'ID Admin: ' + (idAdmin || 'KOSONG') + '\n';
            errorMsg += 'Email: ' + (email || 'KOSONG') + '\n\n';
            errorMsg += 'window.accountState.currentIdAdmin: ' + (window.accountState?.currentIdAdmin || 'KOSONG');
            
            alert(errorMsg);
            return false;
        }
    });
}

// ===== INIT ON DOM READY =====
document.addEventListener('DOMContentLoaded', function () {
    console.log('🔧 account.js DOMContentLoaded');
    
    // Setup form listener
    setupAccountForm();
    
    // 🔐 Toggle password visibility untuk FORM EDIT ADMIN
    const togglePasswordAdmin = document.getElementById('togglePasswordAdmin');
    const passwordInput = document.getElementById('password');
    const eyeIconAdmin = document.getElementById('eyeIconAdmin');

    if (togglePasswordAdmin && passwordInput && eyeIconAdmin) {
        togglePasswordAdmin.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIconAdmin.src = '/assets/icons/show.png';
            } else {
                passwordInput.type = 'password';
                eyeIconAdmin.src = '/assets/icons/hide.png';
            }
        });
    }

    // Validasi form submit: password wajib untuk akun baru
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

    // 🔐 Modal close on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            });
            // Juga tutup modal petugas jika aktif
            const modalPetugas = document.getElementById('modalPetugas');
            if (modalPetugas?.classList.contains('active')) {
                closeModalPetugas();
            }
        }
    });

    // 🔐 Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // ===== MODAL PETUGAS EVENT LISTENERS =====
    const btnClosePetugas = document.getElementById('btnClosePetugasModal');
    const btnBatalPetugas = document.getElementById('btnBatalPetugas');
    const modalPetugas = document.getElementById('modalPetugas');

    btnClosePetugas?.addEventListener('click', closeModalPetugas);
    btnBatalPetugas?.addEventListener('click', closeModalPetugas);

    modalPetugas?.addEventListener('click', (e) => {
        if (e.target.id === 'modalPetugas') closeModalPetugas();
    });

    // ===== FORM SUBMIT HANDLER UNTUK PETUGAS =====
    const formPetugas = document.getElementById('formPetugas');
    if (formPetugas) {
        formPetugas.addEventListener('submit', async (e) => {
            e.preventDefault();

            const petugasId = document.getElementById('petugasId');
            const isEdit = petugasId && petugasId.value !== '';
            const btnSimpan = document.getElementById('btnSimpanPetugas');

            if (btnSimpan) {
                btnSimpan.disabled = true;
                btnSimpan.textContent = isEdit ? 'Menyimpan...' : 'Menambahkan...';
            }

            const formData = new FormData(formPetugas);
            let url = window.AccountConfig?.routes?.petugasStore || '/admin/petugas';

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

    // 🔍 Init search filter
    initAccountSearch();
    
    // ===== OVERRIDE FUNGSI verifyOTP =====
    window.verifyOTP = function() {
        console.log('🎯 [OVERRIDE] verifyOTP() dipanggil!');
        
        const otpInput = document.getElementById('otpInput');
        const otp = otpInput?.value.trim();
        const statusDiv = document.getElementById('otpVerifyStatus');
        
        // Ambil email dari berbagai sumber
        let email = window.accountState?.currentEmail;
        
        if (!email) {
            email = document.getElementById('otp_email_hidden')?.value;
            console.log('📧 Email dari hidden input:', email);
        }
        
        if (!email) {
            email = document.getElementById('otpTargetEmail')?.textContent?.trim();
            console.log('📧 Email dari span display:', email);
        }
        
        if (!email) {
            email = document.getElementById('otpVerifyModal')?.dataset?.email;
            console.log('📧 Email dari data attribute:', email);
        }
        
        console.log('🔢 OTP:', otp, '| 📧 Email final:', email);
        
        // Validasi OTP
        if (!otp || otp.length !== 4 || !/^\d+$/.test(otp)) {
            const msg = 'OTP harus 4 digit angka';
            if (statusDiv) statusDiv.innerHTML = '<div style="color:#dc3545;">'+msg+'</div>';
            else alert(msg);
            return;
        }
        
        // Validasi Email
        if (!email || !email.includes('@')) {
            const msg = 'Email tidak valid atau tidak ditemukan';
            if (statusDiv) statusDiv.innerHTML = '<div style="color:#dc3545;">'+msg+'</div>';
            else alert('❌ ' + msg);
            console.error('❌ Email tidak valid:', email);
            return;
        }
        
        // Loading state
        const verifyBtn = document.querySelector('#otpVerifyModal button[onclick="verifyOTP()"]');
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Memverifikasi...';
        }
        
        console.log('📡 Mengirim request ke:', window.AccountConfig?.routes?.verifyOtp || '/admin/akun/verify-otp');
        
        fetch(window.AccountConfig?.routes?.verifyOtp || '/admin/akun/verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.AccountConfig?.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email, otp: otp })
        })
        .then(async res => {
            console.log('📦 Response status:', res.status);
            const data = await res.json();
            console.log('📦 Response data:', data);
            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                console.log('OTP BERHASIL TERKIRIM! Menampilkan form...');
                
                if (statusDiv) statusDiv.innerHTML = '<div style="color:#20A726;">✅ OTP valid!</div>';
                
                // Tutup modal OTP
                const otpModal = document.getElementById('otpVerifyModal');
                if (otpModal) otpModal.style.display = 'none';
                
                // Tampilkan form
                const formSection = document.getElementById('formSection');
                if (formSection) {
                    console.log('📋 Form section ditemukan, menampilkan...');
                    formSection.style.display = 'block';
                    formSection.scrollIntoView({ behavior: 'smooth' });
                    
                    // Isi field form
                    if (email) {
                        const emailInput = document.getElementById('email');
                        if (emailInput) {
                            emailInput.value = email;
                            console.log('✅ Email diisi:', email);
                        }
                    }
                    
                    const idInput = document.getElementById('formIdAdmin');
                    const idAdmin = window.accountState?.currentIdAdmin;
                    
                    if (idInput && idAdmin) {
                        idInput.value = idAdmin;
                        console.log('✅ ID Admin diisi:', idAdmin);
                    } else {
                        console.error('❌ formIdAdmin atau currentIdAdmin kosong!');
                        console.log('idInput:', idInput);
                        console.log('currentIdAdmin:', idAdmin);
                    }
                    
                    const formTitle = document.getElementById('formTitle');
                    if (formTitle) formTitle.textContent = 'Edit Akun Admin';
                    
                    // Setup form action lagi
                    setupAccountForm();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Silakan ubah email ataupun password',
                        confirmButtonColor: '#20A726',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });

                } else {
                    console.error('❌ FATAL: #formSection TIDAK DITEMUKAN!');
                    alert('Error: Form tidak ditemukan.');
                }
            } else {
                console.error('❌ OTP GAGAL:', data.message);
                const msg = data.message || 'OTP tidak valid';
                if (statusDiv) statusDiv.innerHTML = '<div style="color:#dc3545;">'+msg+'</div>';
                else Swal.fire('Gagal', msg, 'error');
            }
        })
        .catch(err => {
            console.error('❌ NETWORK ERROR:', err);
            const msg = 'Terjadi kesalahan koneksi';
            if (statusDiv) statusDiv.innerHTML = '<div style="color:#dc3545;">'+msg+'</div>';
            else Swal.fire('Error', msg, 'error');
        })
        .finally(() => {
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verifikasi';
            }
        });
    };
    
    // Pastikan executeAction tersedia
    if (typeof window.executeAction !== 'function') {
        window.executeAction = function() {
            console.log('🎯 [OVERRIDE] executeAction() dipanggil');
            const { currentAction, currentIdAdmin, currentEmail } = window.accountState || {};
            
            if (currentAction === 'edit') {
                const formSection = document.getElementById('formSection');
                if (formSection) {
                    formSection.style.display = 'block';
                    formSection.scrollIntoView({ behavior: 'smooth' });
                    
                    if (currentEmail) {
                        const emailInput = document.getElementById('email');
                        if (emailInput) emailInput.value = currentEmail;
                    }
                    if (currentIdAdmin) {
                        const idInput = document.getElementById('formIdAdmin');
                        if (idInput) idInput.value = currentIdAdmin;
                    }
                    
                    const formTitle = document.getElementById('formTitle');
                    if (formTitle) formTitle.textContent = 'Edit Akun Admin';
                    
                    alert('Form edit ditampilkan!');
                }
            }
        };
    }
    
    console.log('account.js override selesai. Fungsi ready.');
});

// ===== MODAL HELPERS =====

/**
 * FUNGSI: Menampilkan modal
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

/**
 * FUNGSI: Menyembunyikan modal
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * FUNGSI: Close modal + reset OTP form jika perlu
 */
function closeModal(modalId) {
    hideModal(modalId);
    
    // Reset OTP form jika modal yang ditutup adalah otpVerifyModal
    if (modalId === 'otpVerifyModal') {
        const otpInput = document.getElementById('otpInput');
        const otpStatus = document.getElementById('otpVerifyStatus');
        if (otpInput) otpInput.value = '';
        if (otpStatus) otpStatus.innerHTML = '';
    }
}

// ===== ADMIN ACCOUNT FUNCTIONS =====

/**
 * FUNGSI: Fetch decrypted password dari server (hanya untuk edit mode)
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
 * FUNGSI: Tampilkan form tambah akun baru
 */
function showAddForm() {
    const totalAccounts = document.querySelectorAll('.account-card').length;
    if (totalAccounts >= 3) {
        showModal('limitModal');
        return;
    }

    resetForm();
    document.getElementById('formTitle').textContent = 'Tambah Akun Baru';
    const formSection = document.getElementById('formSection');
    if (formSection) {
        formSection.style.display = 'block';
        formSection.scrollIntoView({ behavior: 'smooth' });
    }
    window.accountState.isEditing = false;
}

/**
 * FUNGSI: Buat akun utama dengan data default
 */
function createDefaultAccount() {
    resetForm();
    document.getElementById('formTitle').textContent = 'Buat Akun Utama';
    document.getElementById('email').value = 'simpelsi2025@gmail.com';
    document.getElementById('password').value = 'Admin123';
    const formSection = document.getElementById('formSection');
    if (formSection) {
        formSection.style.display = 'block';
        formSection.scrollIntoView({ behavior: 'smooth' });
    }
    window.accountState.isEditing = false;
}

/**
 * FUNGSI: Reset form ke state awal
 */
function resetForm() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const idInput = document.getElementById('formIdAdmin');
    const eyeIcon = document.getElementById('eyeIconAdmin');

    if (emailInput) emailInput.value = '';
    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.type = 'password';
    }
    if (idInput) idInput.value = '';
    if (eyeIcon) eyeIcon.src = '/assets/icons/hide.png';

    window.accountState.isEditing = false;
}

/**
 * FUNGSI: Request OTP untuk action sensitif (edit/delete akun admin)
 * PARAM: action = 'edit' | 'delete', idAdmin = number, email = string
 */
function requestOTPForAction(action, idAdmin, email) {
        // ✅ DEBUG: Log parameter yang diterima ← 🎯 TAMBAHKAN INI DI SINI
    console.log('🔍 requestOTPForAction called with:', { action, idAdmin, email });
    console.log(' Type of idAdmin:', typeof idAdmin);
    
    // ✅ VALIDASI: Pastikan idAdmin ada
    if (!idAdmin || idAdmin === '' || idAdmin === 'null' || idAdmin === 'undefined') {
        console.error('❌ ERROR: idAdmin is empty or invalid!');
        alert('Error: ID Admin tidak valid. Silakan refresh halaman dan coba lagi.');
        return;
    }
    // ✅ Simpan state ke window.accountState
    window.accountState.currentAction = action;
    window.accountState.currentIdAdmin = idAdmin;
    window.accountState.currentEmail = email;

    // Update modal content
    const targetDisplay = document.getElementById('targetEmailDisplay');
    const otpTarget = document.getElementById('otpTargetEmail');
    if (targetDisplay) targetDisplay.textContent = email;
    if (otpTarget) otpTarget.textContent = email;

    // Set judul modal
    let title = 'Verifikasi ';
    if (action === 'edit') title += 'Edit Akun';
    else if (action === 'delete') title += 'Hapus Akun';
    const modalTitle = document.getElementById('otpModalTitle');
    if (modalTitle) modalTitle.textContent = title;

    // Clear status sebelumnya
    const statusDiv = document.getElementById('otpRequestStatus');
    if (statusDiv) statusDiv.innerHTML = '';

    showModal('otpRequestModal');
}

/**
 * FUNGSI: Kirim request OTP ke backend via AJAX
 */
async function sendOTPToTarget() {
    const email = window.accountState.currentEmail;
    const statusDiv = document.getElementById('otpRequestStatus');

    try {
        const response = await fetch(window.AccountConfig?.routes?.requestOtp || '/admin/akun/request-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.AccountConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        });

        const data = await response.json();

        if (data.status === 'success' || data.status === 'success_dev') {
            let msg = `<div style="color:#20A726;">OTP dikirim ke ${email}</div>`;
            if (data.status === 'success_dev' && data.otp) {
                msg = `<div style="background:#e6f7e6; padding:8px; border-radius:4px; color:#095E0D;">[DEV] Gunakan kode: <strong>${data.otp}</strong></div>`;
            }
            if (statusDiv) statusDiv.innerHTML = msg;

            // Auto-proceed ke modal verify setelah delay singkat
            setTimeout(() => {
                hideModal('otpRequestModal');
                showModal('otpVerifyModal');

                // Pre-fill OTP di dev mode
                if (data.otp) {
                    const otpInput = document.getElementById('otpInput');
                    if (otpInput) otpInput.value = data.otp;
                }
                const otpInput = document.getElementById('otpInput');
                if (otpInput) otpInput.focus();
            }, 800);
        } else {
            if (statusDiv) statusDiv.innerHTML = `<div class="alert-error">${data.message}</div>`;
        }
    } catch (error) {
        console.error('OTP request failed:', error);
        if (statusDiv) statusDiv.innerHTML = '<div class="alert-error">❌ Gagal kirim OTP.</div>';
    }
}

/**
 * FUNGSI: Verifikasi kode OTP yang dimasukkan user - WITH DEBUG LOGS
 */
async function verifyOTP() {
    console.log('🔍 [DEBUG] verifyOTP() STARTED');
    
    const otpInput = document.getElementById('otpInput');
    const otp = otpInput?.value.trim();
    const statusDiv = document.getElementById('otpVerifyStatus');

    console.log('🔢 OTP entered:', otp);

    // Validasi input: harus 4 digit angka
    if (!otp || otp.length !== 4 || !/^\d+$/.test(otp)) {
        if (statusDiv) statusDiv.innerHTML = '<div class="alert-error">OTP harus 4 digit angka.</div>';
        console.error('❌ OTP validation failed');
        return;
    }

    try {
        console.log('📡 Sending verify request to:', window.AccountConfig?.routes?.verifyOtp || '/admin/akun/verify-otp');
        
        const response = await fetch(window.AccountConfig?.routes?.verifyOtp || '/admin/akun/verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.AccountConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: window.accountState.currentEmail,
                otp: otp
            })
        });

        console.log('📦 Response status:', response.status);
        const data = await response.json();
        console.log('📦 Response data:', data);

        if (data.status === 'success') {
            console.log('OTP verified successfully!');
            if (statusDiv) statusDiv.innerHTML = `<div style="color:#20A726;">OTP valid. Memproses...</div>`;

            setTimeout(() => {
                console.log('🔄 Calling hideModal and executeAction...');
                hideModal('otpVerifyModal');
                executeAction();
            }, 600);
        } else {
            console.error('❌ OTP verification failed:', data.message);
            if (statusDiv) statusDiv.innerHTML = `<div class="alert-error">${data.message}</div>`;
        }
    } catch (error) {
        console.error('❌ Network error in verifyOTP:', error);
        if (statusDiv) statusDiv.innerHTML = '<div class="alert-error">❌ Kesalahan jaringan.</div>';
    }
}



/**
 * FUNGSI: Eksekusi action (edit atau delete) setelah OTP terverifikasi - WITH DEBUG LOGS
 */
function executeAction() {
    console.log('🎯 [DEBUG] executeAction() STARTED');
    console.log('📦 window.accountState:', window.accountState);
    
    const { currentAction, currentIdAdmin, currentEmail } = window.accountState;

    console.log('🔍 currentAction:', currentAction);
    console.log('🔍 currentIdAdmin:', currentIdAdmin);
    console.log('🔍 currentEmail:', currentEmail);

    if (currentAction === 'edit') {
        console.log('✅ Action = "edit", attempting to show form...');
        
        // ✅ Tampilkan section form edit admin
        const formSection = document.getElementById('formSection');
        console.log('📋 formSection element:', formSection);
        
        if (!formSection) {
            console.error('❌ FATAL: #formSection NOT FOUND in DOM!');
            console.error('💡 Cek modals.blade.php: Pastikan ada <div id="formSection">');
            alert('Error: Form section tidak ditemukan! Cek console untuk detail.');
            return;
        }
        
        console.log('✨ Setting formSection.style.display = "block"');
        formSection.style.display = 'block';
        formSection.scrollIntoView({ behavior: 'smooth' });
        console.log('✅ Form should be visible now!');
        
        // ✅ Isi field form dengan data existing
        const formIdInput = document.getElementById('formIdAdmin');
        const emailInput = document.getElementById('email');
        const formTitle = document.getElementById('formTitle');
        
        console.log('📝 Filling form fields...');
        if (formIdInput) { formIdInput.value = currentIdAdmin; console.log('✅ formIdAdmin filled'); }
        if (emailInput) { emailInput.value = currentEmail; console.log('✅ email filled'); }
        if (formTitle) { formTitle.textContent = 'Edit Akun Admin'; console.log('✅ title updated'); }

        // ✅ Fetch placeholder password untuk display
        console.log('🔐 Fetching password placeholder...');
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
            console.log('🔐 Password response:', data);
            if (data.status === 'success') {
                const passwordInput = document.getElementById('password');
                if (passwordInput) {
                    passwordInput.value = data.password;
                    window.accountState.isEditing = true;
                    console.log('✅ Password field filled');
                }
            }
        })
        .catch(err => {
            console.error('❌ Password fetch error:', err);
        });

    } else if (currentAction === 'delete') {
        console.log('🗑️ Action = "delete"');
        if (confirm(`Yakin hapus akun ${currentEmail}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/akun/${currentIdAdmin}`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    } else {
        console.error('❌ Unknown action:', currentAction);
    }
}


// ===== PETUGAS CRUD FUNCTIONS =====
/**
 * FUNGSI: Konfirmasi hapus petugas dengan SweetAlert2 + AJAX DELETE
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
 */
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.style.display = 'none';
}

/**
 * FUNGSI: Membuka modal petugas untuk mode tambah atau edit
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
 */
function closeModalPetugas() {
    const modal = document.getElementById('modalPetugas');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('formPetugas')?.reset();
}

// ===== SEARCH FUNCTION =====

/**
 * FUNGSI: Filter akun & petugas client-side berdasarkan keyword
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