/**
 * Script untuk halaman Kelola Akun Admin
 * File: public/js/account.js
 * Project: SIMPELSI - DLH Resik
 */

// ===== STATE GLOBAL =====
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
    const accountForm = document.getElementById('accountForm');
    const idInput = document.getElementById('formIdAdmin');
    
    if (!accountForm) {
        console.error('❌ accountForm tidak ada!');
        return;
    }
    
    console.log('🔧 Setup form action listener...');
    
    // Hapus listener lama (mencegah duplikasi)
    const newForm = accountForm.cloneNode(true);
    accountForm.parentNode.replaceChild(newForm, accountForm);
    
    newForm.addEventListener('submit', function(e) {
        let idAdmin = idInput?.value?.trim();
        
        if (!idAdmin || idAdmin === '') {
            idAdmin = window.accountState?.currentIdAdmin;
        }
        
        const email = document.getElementById('email')?.value?.trim();
        
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
            
            console.log('✅ Form ready: PUT /admin/akun/' + idAdmin);
        } else {
            e.preventDefault();
            console.error('❌ Validasi gagal:', { idAdmin, email });
            alert('Email dan ID Admin wajib diisi!');
            return false;
        }
    });
}

// ===== FETCH PASSWORD PLACEHOLDER =====
async function fetchPasswordPlaceholder(idAdmin) {
    if (!idAdmin) return;
    
    try {
        const response = await fetch('/admin/akun/ajax/get-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: `id_admin=${encodeURIComponent(idAdmin)}`
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.value = data.password || '••••••••';
                window.accountState.isEditing = true;
            }
        }
    } catch (error) {
        console.error('❌ Password fetch error:', error);
    }
}

// ===== MODAL HELPERS =====
function showModal(modalId) {
    console.log('📂 showModal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => { modal.classList.add('active'); }, 10);
        console.log('✅ Modal displayed');
    } else {
        console.error('❌ Modal not found:', modalId);
    }
}

function hideModal(modalId) {
    console.log('📂 hideModal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
        console.log('✅ Modal hidden');
    }
}

function closeModal(modalId) {
    hideModal(modalId);
    if (modalId === 'otpVerifyModal') {
        const otpInput = document.getElementById('otpInput');
        const otpStatus = document.getElementById('otpVerifyStatus');
        if (otpInput) otpInput.value = '';
        if (otpStatus) otpStatus.innerHTML = '';
    }
}

function closeEditAdminModal() {
    hideModal('editAdminModal');
}

// ===== ADMIN ACCOUNT FUNCTIONS =====
function resetForm() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const idInput = document.getElementById('formIdAdmin');
    const eyeIcon = document.getElementById('eyeIconAdmin');

    if (emailInput) emailInput.value = '';
    if (passwordInput) { passwordInput.value = ''; passwordInput.type = 'password'; }
    if (idInput) idInput.value = '';
    if (eyeIcon) eyeIcon.src = '/assets/icons/hide.png';
    window.accountState.isEditing = false;
}

function requestOTPForAction(action, idAdmin, email) {
    console.log('🔍 requestOTPForAction:', { action, idAdmin, email });
    
    if (!idAdmin || !email) {
        alert('Error: Data tidak valid!');
        return;
    }
    
    window.accountState.currentAction = action;
    window.accountState.currentIdAdmin = idAdmin;
    window.accountState.currentEmail = email;
    
    const targetDisplay = document.getElementById('targetEmailDisplay');
    const otpTarget = document.getElementById('otpTargetEmail');
    if (targetDisplay) targetDisplay.textContent = email;
    if (otpTarget) otpTarget.textContent = email;
    
    const modalTitle = document.getElementById('otpModalTitle');
    if (modalTitle) {
        modalTitle.textContent = action === 'edit' ? 'Verifikasi Edit Akun' : '🗑️ Verifikasi Hapus Akun';
    }
    
    const statusDiv = document.getElementById('otpRequestStatus');
    if (statusDiv) statusDiv.innerHTML = '';
    
    showModal('otpRequestModal');
}

async function sendOTPToTarget() {
    const email = window.accountState.currentEmail;
    const statusDiv = document.getElementById('otpRequestStatus');

    // ✅ RESET: Bersihkan notif sebelum proses baru
    if (statusDiv) {
        statusDiv.innerHTML = '';
        statusDiv.className = '';
        statusDiv.style.display = 'none';
    }

    if (!email) {
        if (statusDiv) {
            statusDiv.style.display = 'block';
            statusDiv.className = 'show alert-error';
            statusDiv.innerHTML = '❌ Email tidak ditemukan!';
        }
        return;
    }

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
            let msg = 'OTP berhasil dikirim ke email Anda!';
            if (data.status === 'success_dev' && data.otp) {
                msg = `OTP: <strong>${data.otp}</strong><br><small>(Development mode)</small>`;
            }
            
            if (statusDiv) {
                statusDiv.style.display = 'block';
                statusDiv.className = 'show alert-success';
                statusDiv.innerHTML = msg;
            }

            setTimeout(() => {
                hideModal('otpRequestModal');
                setTimeout(() => {
                    showModal('otpVerifyModal');
                    document.getElementById('otpInput')?.focus();
                }, 300);
            }, 1500);
            
        } else {
            if (statusDiv) {
                statusDiv.style.display = 'block';
                statusDiv.className = 'show alert-error';
                statusDiv.innerHTML = data.message || 'Gagal mengirim OTP';
            }
        }
    } catch (error) {
        console.error('OTP Error:', error);
        if (statusDiv) {
            statusDiv.style.display = 'block';
            statusDiv.className = 'show alert-error';
            statusDiv.innerHTML = '❌ Terjadi kesalahan. Coba lagi.';
        }
    }
}

// ===== FUNGSI UTAMA: VERIFY OTP =====
async function verifyOTP() {
    console.log('🔍 verifyOTP() STARTED');
    
    const otpInput = document.getElementById('otpInput');
    const otp = otpInput?.value.trim();
    const statusDiv = document.getElementById('otpVerifyStatus');
    const email = window.accountState?.currentEmail;

    if (!otp || otp.length !== 4 || !/^\d+$/.test(otp)) {
        if (statusDiv) { statusDiv.className = 'show alert-error'; statusDiv.innerHTML = '⚠️ Masukkan 4 digit angka!'; }
        return;
    }

    if (!email) {
        if (statusDiv) { statusDiv.className = 'show alert-error'; statusDiv.innerHTML = '❌ Email tidak ditemukan!'; }
        return;
    }

    try {
        const response = await fetch(window.AccountConfig?.routes?.verifyOtp || '/admin/akun/verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.AccountConfig?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email, otp: otp })
        });

        const data = await response.json();

        // ✅ JIKA OTP BERHASIL
        if (data.status === 'success') {
            if (statusDiv) { 
                statusDiv.className = 'show alert-success'; 
                statusDiv.innerHTML = 'OTP valid! Memproses...'; 
            }
            
            // Reset input & status
            if (otpInput) otpInput.value = '';
            if (statusDiv) {
                statusDiv.innerHTML = '';
                statusDiv.className = ''; 
            }
            
            // Tutup modal OTP
            hideModal('otpVerifyModal');
            
            // ✅ LANGKAH 1: Tampilkan SweetAlert DULU
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'OTP terverifikasi. Membuka form edit...',
                confirmButtonColor: '#20A726',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // LANGKAH 2: Setelah SweetAlert selesai, baru buka modal edit
                const editModal = document.getElementById('editAdminModal');
                if (editModal) {
                    editModal.style.display = 'flex';
                    setTimeout(() => { editModal.classList.add('active'); }, 10);
                    
                    // Isi field form
                    if (email) document.getElementById('email').value = email;
                    if (window.accountState?.currentIdAdmin) {
                        document.getElementById('formIdAdmin').value = window.accountState.currentIdAdmin;
                    }
                    document.getElementById('editAdminModalTitle').textContent = 'Edit Akun Admin';
                    
                    // Setup form & fetch password
                    setupAccountForm();
                    fetchPasswordPlaceholder(window.accountState?.currentIdAdmin);
                    
                } else {
                    console.error('❌ editAdminModal not found!');
                    Swal.fire('Error', 'Modal edit tidak ditemukan!', 'error');
                }
                });
            
                // ❌ JIKA OTP GAGAL / SALAH
                } else {
                    if (statusDiv) { 
                        statusDiv.className = 'show alert-error'; 
                        statusDiv.innerHTML = data.message || '❌ OTP tidak valid!'; 
                    }
                    
                    // Reset input biar bisa ketik ulang
                    if (otpInput) {
                        otpInput.value = '';
                        otpInput.focus();
                    }
                }
                
                // ❌ JIKA ERROR NETWORK / SERVER
                } catch (error) {
                    console.error(' Network error:', error);
                    if (statusDiv) { 
                        statusDiv.className = 'show alert-error'; 
                        statusDiv.innerHTML = '❌ Kesalahan koneksi!'; 
                    }
                    
                    // Reset input kalau error
                    if (otpInput) {
                        otpInput.value = ''; 
                    }
                }
            }

// ===== EXECUTE ACTION (fallback) =====
function executeAction() {
    const { currentAction, currentIdAdmin, currentEmail } = window.accountState;
    
    if (currentAction === 'edit') {
        const editModal = document.getElementById('editAdminModal');
        if (editModal) {
            editModal.style.display = 'flex';
            setTimeout(() => { editModal.classList.add('active'); }, 10);
            
            document.getElementById('formIdAdmin').value = currentIdAdmin || '';
            document.getElementById('email').value = currentEmail || '';
            document.getElementById('editAdminModalTitle').textContent = 'Edit Akun Admin';
            
            setupAccountForm();
            fetchPasswordPlaceholder(currentIdAdmin);
        }
    } else if (currentAction === 'delete') {
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
    }
}

// ===== INIT ON DOM READY =====
document.addEventListener('DOMContentLoaded', function () {
    console.log('🔧 account.js DOMContentLoaded');
    
    setupAccountForm();
    
    // Toggle password visibility
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
    
    // Toggle password visibility untuk Petugas
    const togglePasswordPetugas = document.getElementById('togglePasswordPetugas');
    const passwordPetugasInput = document.getElementById('passwordPetugas');
    const eyeIconPetugas = document.getElementById('eyeIconPetugas');
    
    if (togglePasswordPetugas && passwordPetugasInput && eyeIconPetugas) {
        togglePasswordPetugas.addEventListener('click', function () {
            if (passwordPetugasInput.type === 'password') {
                passwordPetugasInput.type = 'text';
                eyeIconPetugas.src = '/assets/show.png';
            } else {
                passwordPetugasInput.type = 'password';
                eyeIconPetugas.src = '/assets/hide.png';
            }
        });
    }

    // Password validation for new accounts
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

    // Close modal on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                if (modal.style.display === 'flex' || modal.classList.contains('active')) {
                    hideModal(modal.id);
                }
            });
        }
    });

    // Close modal on outside click (KECUALI editAdminModal)
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function (e) {
            // ❌ JANGAN tutup editAdminModal saat klik di luar
            if (e.target === this && this.id !== 'editAdminModal') {
                hideModal(this.id);
            }
        });
    });

    // Modal petugas listeners
    const btnClosePetugas = document.getElementById('btnClosePetugasModal');
    const btnBatalPetugas = document.getElementById('btnBatalPetugas');
    const modalPetugas = document.getElementById('modalPetugas');

    btnClosePetugas?.addEventListener('click', closeModalPetugas);
    btnBatalPetugas?.addEventListener('click', closeModalPetugas);
    modalPetugas?.addEventListener('click', (e) => { if (e.target.id === 'modalPetugas') closeModalPetugas(); });

// Form petugas submit
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
                    // ✅ LANGKAH 1: Tutup modal DULU (tanpa animasi) sebelum SweetAlert
                    const modal = document.getElementById('modalPetugas');
                    if (modal) {
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                        document.body.style.overflow = ''; // Unlock scroll
                    }
                    
                    // ✅ LANGKAH 2: Baru tampilkan SweetAlert
                    Swal.fire({ 
                        title: 'Berhasil!', 
                        text: isEdit ? 'Data petugas telah diperbarui.' : 'Petugas baru berhasil ditambahkan.', 
                        icon: 'success', 
                        confirmButtonColor: '#20A726', 
                        timer: 2000, 
                        showConfirmButton: false 
                    });
                    
                    // ✅ LANGKAH 3: Reload setelah SweetAlert selesai
                    setTimeout(() => {
                        location.reload();
                    }, 2100); // Sedikit lebih lama dari timer SweetAlert
                    
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
                alert('❌ Gagal: Server error.');
            }
        } catch (error) { 
            console.error('Fetch Error:', error); 
            alert('❌ Network error: ' + error.message); 
        }
        finally { 
            if (btnSimpan) { 
                btnSimpan.disabled = false; 
                btnSimpan.textContent = isEdit ? 'Update' : 'Simpan'; 
            } 
        }
    });
}

    // Search filter
    initAccountSearch();
    
    console.log('✅ account.js ready!');
});

// ===== PETUGAS FUNCTIONS =====
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
    })
    .then((result) => {
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
                    // ✅ NOTIFIKASI SUKSES - OTOMATIS TUTUP (TANPA TOMBOL OK)
                    Swal.fire({ 
                        title: 'Terhapus!', 
                        text: data.message || 'Akun petugas berhasil dihapus.', 
                        icon: 'success', 
                        confirmButtonColor: '#20A726',
                        timer: 2000,           // ✅ Otomatis tutup setelah 2 detik
                        showConfirmButton: false  // ✅ TIDAK ADA TOMBOL OK
                    });
                    
                    // ✅ Reload halaman setelah notif hilang
                    setTimeout(() => {
                        location.reload();
                    }, 2100);
                }
                else { 
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

function closeDeleteModal() { const modal = document.getElementById('deleteModal'); if (modal) modal.style.display = 'none'; }

function openPetugasModal(mode, data = null) {
    const modal = document.getElementById('modalPetugas');
    const title = document.getElementById('modalPetugasTitle');
    const passHint = document.getElementById('passHint');
    const btnSimpan = document.getElementById('btnSimpanPetugas');

    if (!modal) { 
        alert('⚠️ Modal tidak ditemukan!'); 
        return; 
    }

    // ✅ PENTING: RESET FORM DULU SEBELUM MODAL DITAMPILKAN
    const formPetugas = document.getElementById('formPetugas');
    if (formPetugas) formPetugas.reset();
    
    // Reset field ID dan password secara eksplisit
    const petugasId = document.getElementById('petugasId');
    if (petugasId) petugasId.value = '';
    
    const passwordPetugas = document.getElementById('passwordPetugas');
    if (passwordPetugas) {
        passwordPetugas.value = '';
        passwordPetugas.required = true; // Wajib untuk akun baru
    }
    
    // Reset dropdown wilayah
    const levelSelect = document.getElementById('levelPetugas');
    if (levelSelect) levelSelect.value = '';

    // Baru tampilkan modal setelah form bersih
    showModal('modalPetugas');
    document.body.style.overflow = 'hidden';

    if (mode === 'edit' && data) {
        // ===== MODE EDIT =====
        if (title) title.textContent = 'Edit Akun Petugas';
        if (btnSimpan) btnSimpan.textContent = 'Update';
        
        // Isi data pakai .value (BUKAN setAttribute)
        if (petugasId && data.id) petugasId.value = data.id;
        
        const namaInput = document.getElementById('namaLengkap');
        if (namaInput && data.nama) namaInput.value = data.nama;
        
        const emailInput = document.getElementById('emailPetugas');
        if (emailInput && data.email) emailInput.value = data.email;
        
        const telpInput = document.getElementById('noTelepon');
        if (telpInput && data.telpon) telpInput.value = data.telpon;
        
        if (levelSelect && data.level) levelSelect.value = data.level;
        
        // Password tidak wajib untuk edit
        if (passwordPetugas) passwordPetugas.required = false;
        
        if (passwordPetugas) {
            passwordPetugas.placeholder = 'Kosongkan jika tidak ingin mengubah';
            passwordPetugas.required = false;
        }
        
    } else {
        // ===== MODE TAMBAH (BARU) =====
        if (title) title.textContent = 'Tambah Akun Petugas';
        if (btnSimpan) btnSimpan.textContent = 'Simpan';
        if (passHint) passHint.textContent = '';
        
        // Pastikan semua field benar-benar kosong
        if (formPetugas) formPetugas.reset();
        if (petugasId) petugasId.value = '';
        
        const namaInput = document.getElementById('namaLengkap');
        const emailInput = document.getElementById('emailPetugas');
        const telpInput = document.getElementById('noTelepon');
        
        if (namaInput) namaInput.value = '';
        if (emailInput) emailInput.value = '';
        if (telpInput) telpInput.value = '';
        if (levelSelect) levelSelect.value = '';
        if (passwordPetugas) {
            passwordPetugas.value = '';
            passwordPetugas.required = true;
        }
    }
}

function closeModalPetugas() {
    const modal = document.getElementById('modalPetugas');
    if (modal) { modal.classList.remove('active'); modal.style.display = 'none'; }
    document.body.style.overflow = '';
    document.getElementById('formPetugas')?.reset();
}

function initAccountSearch() {
    const searchInput = document.getElementById('searchAkun');
    if (!searchInput) return;
    searchInput.addEventListener('input', function (e) {
        const keyword = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.account-card').forEach(card => { card.style.display = card.textContent.toLowerCase().includes(keyword) ? '' : 'none'; });
        document.querySelectorAll('.petugas-table-container tbody tr').forEach(row => { row.style.display = row.textContent.toLowerCase().includes(keyword) ? '' : 'none'; });
    });
}

// Event delegation untuk toggle password
document.addEventListener('click', function(e) {
    if (e.target.id === 'togglePasswordAdmin' || e.target.closest('#togglePasswordAdmin')) {
        const passwordInput = document.getElementById('password');
        const eyeIconAdmin = document.getElementById('eyeIconAdmin');
        
        if (passwordInput && eyeIconAdmin) {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIconAdmin.src = '/assets/icons/show.png';
            } else {
                passwordInput.type = 'password';
                eyeIconAdmin.src = '/assets/icons/hide.png';
            }
        }
    }
});