@extends('layouts.admin')

@section('title', 'Kelola Akun - SIMPELSI')
@section('page-title', 'Kelola Akun')
@section('page-title-mobile', 'AKUN')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
@endpush

{{-- Messages --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

@section('content')
<div class="content-header">
    <h2>Kelola Akun Admin</h2>
</div>

{{-- 🔍 Search Bar Modern (Nama & Email) --}}
<div class="search-wrapper-akun">
    <i class="fas fa-search search-icon"></i>
    <input type="text" id="searchAkun" class="search-input-akun" placeholder="Cari akun petugas berdasarkan nama atau email">
</div>

{{-- ==================== BAGIAN 1: CARDS ADMIN ==================== --}}
<div class="accounts-grid">
    {{-- Akun Utama --}}
    <div class="account-card">
        <div class="card-header">
            <div class="card-title"><span>🔒</span> Akun Utama WEB</div>
            <span class="badge-default">DEFAULT</span>
        </div>
        <div class="account-info">
            <label>Email:</label>
            <span>{{ $akunUtama ? htmlspecialchars($akunUtama->email) : 'Belum dibuat' }}</span>
        </div>
        <div class="account-info">
            <label>Password:</label>
            <span>••••••••</span>
        </div>
        <div class="btn-group">
            @if($akunUtama)
                <button class="btn btn-outline" 
                    onclick="requestOTPForAction('edit_admin', {{ $akunUtama->id_admin }}, '{{ addslashes($akunUtama->email) }}')">
                    Edit
                </button>
            @else
                <button class="btn btn-primary" onclick="showAdminForm()">Buat Akun Utama</button>
            @endif
        </div>
    </div>

    {{-- Akun Kedua --}}
    <div class="account-card">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Akun Kedua WEB</div>
            <span class="badge-default">DEFAULT</span>
        </div>
        <div class="account-info">
            <label>Email:</label>
            <span>{{ isset($tambahan[0]) ? htmlspecialchars($tambahan[0]->email) : 'Belum dibuat' }}</span>
        </div>
        <div class="account-info">
            <label>Password:</label>
            <span>••••••••</span>
        </div>
        <div class="btn-group">
            @if(isset($tambahan[0]))
                <button class="btn btn-outline" 
                    onclick="requestOTPForAction('edit_admin', {{ $tambahan[0]->id_admin }}, '{{ addslashes($tambahan[0]->email) }}')">
                    Edit
                </button>
                <button class="btn btn-danger" 
                    onclick="requestOTPForAction('delete_admin', {{ $tambahan[0]->id_admin }}, '{{ addslashes($tambahan[0]->email) }}')">
                    Hapus
                </button>
            @else
                <button class="btn btn-primary" onclick="showAdminForm()">Tambah Akun</button>
            @endif
        </div>
    </div>
</div>

{{-- ==================== BAGIAN 2: PETUGAS LAPANGAN ==================== --}}
<hr style="margin: 40px 0; border: none; border-top: 2px solid #e0e0e0;">

<div class="petugas-section" style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-top: 20px;">
    
    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="color: #20A726; margin: 0; font-size: 20px; font-weight: 600;">
            Daftar Akun Petugas Mobile
        </h3>
        <button type="button" class="btn-tambah-akun" 
                onclick="openPetugasModal('add')"
                style="background: #20A726; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            + Tambah Akun
        </button>
    </div>

    {{-- Tabel Daftar Petugas --}}
    <div class="petugas-table-container">
        @if($petugas->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #e6f2e6;">
                        <tr>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">No</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Nama Petugas</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Email</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">No Telpon</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Wilayah Kerja</th>
                            <th style="padding: 12px 15px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Kata Sandi</th>
                            <th style="padding: 12px 15px; text-align: center; border-bottom: 2px solid #ddd; font-weight: 600; color: #333;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $levelNames = [
                            'petugas_dlh' => 'Petugas DLH',
                            'bank_sampah_kelurahan_kauman_kauman_nganjuk' => 'BS Kel. Kauman (Kauman, Nganjuk)',
                            'bank_sampah_kramat_bersih_kramat_nganjuk' => 'BS Kramat Bersih (Kramat, Nganjuk)',
                            'bank_sampah_kelurahan_cangkringan_cangkringan_nganjuk' => 'BS Kel. Cangkringan (Cangkringan, Nganjuk)',
                            'bank_sampah_ngudi_sariro_jatirejo_nganjuk' => 'BS Ngudi Sariro (Jatirejo, Nganjuk)',
                            'bank_sampah_margo_utomo_begadung_nganjuk' => 'BS Margo Utomo (Begadung, Nganjuk)',
                            'bank_sampah_sejahtera_kartoharjo_nganjuk' => 'BS Sejahtera (Kartoharjo, Nganjuk)',
                            'bank_sampah_melati_kedungdowo_nganjuk' => 'BS Melati (Kedungdowo, Nganjuk)',
                            'bank_sampah_anggrek_werungotok_nganjuk' => 'BS Anggrek (Werungotok, Nganjuk)',
                            'bank_sampah_sumber_rejeki_werungotok_nganjuk' => 'BS Sumber Rejeki (Werungotok, Nganjuk)',
                            'bank_sampah_beringin_hijau_ringinanom_nganjuk' => 'BS Beringin Hijau (Ringinanom, Nganjuk)',
                            'bank_sampah_ploso_ploso_nganjuk' => 'BS Ploso (Ploso, Nganjuk)',
                            'bank_sampah_mulyo_agung_kudu_kertosono' => 'BS Mulyo Agung (Kudu, Kertosono)',
                            'bank_sampah_estu_sae_petak_bagor' => 'BS Estu Sae (Petak, Bagor)',
                            'bank_sampah_desa_ngangkatan_ngangkatan_rejoso' => 'BS Desa Ngangkatan (Ngangkatan, Rejoso)',
                            'bank_sampah_desa_jegreg_jegreg_lengkong' => 'BS Desa Jegreg (Jegreg, Lengkong)',
                            'bank_sampah_musirkidul_musirkidul_rejoso' => 'BS Musirkidul (Musirkidul, Rejoso)',
                            'bank_sampah_tanjung_tanjunganom_tanjunganom' => 'BS Tanjung (Tanjunganom)',
                            'bank_sampah_flamboyan_loceret_loceret' => 'BS Flamboyan (Loceret)',
                            'bank_sampah_pelita_bogo_nganjuk' => 'BS Pelita (Bogo, Nganjuk)',
                            'bank_sampah_desa_getas_getas_tanjunganom' => 'BS Desa Getas (Getas, Tanjunganom)',
                            'bank_sampah_mbejaji_juwet_ngronggot' => 'BS Mbejaji (Juwet, Ngronggot)',
                            'bank_sampah_kedondong_kedondong_bagor' => 'BS Kedondong (Kedondong, Bagor)',
                            'bank_sampah_sinar_terang_jampes_pace' => 'BS Sinar Terang (Jampes, Pace)',
                            'bank_sampah_desa_blongko_blongko_ngetos' => 'BS Desa Blongko (Blongko, Ngetos)',
                            'bank_sampah_bukur_bukur_patianrowo' => 'BS Bukur (Bukur, Patianrowo)',
                            'bank_sampah_bungur_makmur_bungur_sukomoro' => 'BS Bungur Makmur (Bungur, Sukomoro)',
                            'bank_sampah_seger_waras_mabung_baron' => 'BS Seger Waras (Mabung, Baron)',
                            'bank_sampah_maju_bahagia_gondanglegi_prambon' => 'BS Maju Bahagia (Gondanglegi, Prambon)',
                            'bank_sampah_barokah_kemlokolegi_baron' => 'BS Barokah (Kemlokolegi, Baron)',
                            'bank_sampah_dahlia_senjayan_gondang' => 'BS Dahlia (Senjayan, Gondang)',
                            'bank_sampah_cengkok_cengkok_ngronggot' => 'BS Cengkok (Cengkok, Ngronggot)',
                            'bank_sampah_induk_salepok_omahe_nganjuk_kedondong_bagor' => 'BS Induk Salepok Omahe Nganjuk (Kedondong, Bagor)',
                        ];
                    @endphp
                        @foreach($petugas as $index => $p)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 15px;">{{ $index + 1 }}</td>
                                <td style="padding: 12px 15px;">{{ htmlspecialchars($p->nama_lengkap) }}</td>
                                <td style="padding: 12px 15px;">{{ htmlspecialchars($p->email) }}</td>
                                <td style="padding: 12px 15px;">{{ htmlspecialchars($p->no_telepon) }}</td>
                                <td style="padding: 12px 15px;">
                                <span style="display: inline-block; padding: 4px 12px; background: #e8f5e9; color: #2e7d32; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                    {{ $levelNames[$p->level] ?? $p->level }}
                                </span>
                            </td>
                            
                                <td style="padding: 12px 15px;">••••••••••••</td>
                                <td style="padding: 12px 15px; text-align: center;">
                                    <button type="button" class="btn-edit-modal" 
                                    onclick='openPetugasModal("edit", {{ json_encode([
                                        "id" => $p->id_petugas,
                                        "nama" => $p->nama_lengkap,
                                        "email" => $p->email,
                                        "telpon" => $p->no_telepon,
                                        "level" => $p->level
                                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                    <button onclick='openPetugasModal("edit", ...)' 
                                    style="display: inline-block; margin: 0 2px; padding: 5px 10px; background: #fff3cd; color: #856404; border: none; border-radius: 4px; cursor: pointer; vertical-align: middle;" 
                                    title="Edit">
                                    <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit" style="width: 18px; height: 18px; vertical-align: middle;">
                                    </button>
                                    
                                    <button onclick="confirmDelete({{ $p->id_petugas }})" 
                                    style="display: inline-block; margin: 0 2px; padding: 5px 10px; background: #f8d7da; color: #721c24; border: none; border-radius: 4px; cursor: pointer; vertical-align: middle;"
                                    title="Hapus">
                                    <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus" style="width: 18px; height: 18px; object-fit: contain; display: block;">
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; color: #666;">
                <p style="margin: 0; font-size: 16px;">📭 Belum ada akun petugas lapangan</p>
                <p style="margin: 5px 0 0 0; font-size: 14px;">Silakan tambahkan petugas menggunakan tombol di atas</p>
            </div>
        @endif
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="deleteModal" class="modal" style="display: none;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 400px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h4 style="margin-top: 0; color: #333; font-size: 18px; margin-bottom: 15px;">Konfirmasi Hapus</h4>
        <p style="color: #666; margin-bottom: 25px;">Apakah Anda yakin ingin menghapus Akun ini?</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button onclick="closeDeleteModal()" 
                    style="background: #6c757d; color: white; padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                Batal
            </button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        style="background: #dc3545; color: white; padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ==================== MODALS OTP (DARI PARTIALS) ==================== --}}
@include('admin.account.partials.modals')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@push('scripts')
<script>
// ==================== GLOBAL VARIABLES ====================
let currentAction = null;
let currentId = null;
let currentEmail = null;

// ==================== MODAL HELPERS ====================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'flex';
}

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

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// ==================== ADMIN (WITH OTP) ====================
function showAdminForm() {
    const section = document.getElementById('adminFormSection');
    if (section) section.style.display = 'block';
}

function requestOTPForAction(action, id, email) {
    currentAction = action;
    currentId = id;
    currentEmail = email;
    const display = document.getElementById('targetEmailDisplay');
    if (display) display.textContent = email;
    openModal('otpRequestModal');
}

function sendOTPToTarget() {
    fetch('{{ route("admin.akun.request-otp") }}', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({ email: currentEmail })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success' || data.status === 'success_dev') {
            const targetEmail = document.getElementById('otpTargetEmail');
            if (targetEmail) targetEmail.textContent = currentEmail;
            closeModal('otpRequestModal');
            openModal('otpVerifyModal');
            if (data.otp) alert('Dev Mode - OTP: ' + data.otp);
        } else {
            alert('Gagal kirim OTP');
        }
    })
    .catch(e => { 
        console.error(e); 
        alert('Error'); 
    });
}

function verifyOTP() {
    const otpInput = document.getElementById('otpInput');
    const otp = otpInput ? otpInput.value : '';
    
    if (!otp || otp.length !== 4) {
        const status = document.getElementById('otpVerifyStatus');
        if (status) {
            status.innerHTML = '<div class="alert alert-error">OTP harus 4 digit!</div>';
        }
        return;
    }
    
    fetch('{{ route("admin.akun.verify-otp") }}', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({ email: currentEmail, otp: otp })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            closeModal('otpVerifyModal');
            executeAdminAction();
        } else {
            const status = document.getElementById('otpVerifyStatus');
            if (status) {
                status.innerHTML = '<div class="alert alert-error">OTP tidak valid!</div>';
            }
        }
    })
    .catch(e => { 
        console.error(e); 
        alert('Error'); 
    });
}

function executeAdminAction() {
    if (currentAction === 'edit_admin') {
        loadAdminForEdit(currentId);
    } else if (currentAction === 'delete_admin') {
        deleteAdmin(currentId);
    }
}

function loadAdminForEdit(id) {
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
    
    fetch(`/admin/akun/${id}`)
        .then(r => r.json())
        .then(data => {
            const emailInput = document.getElementById('adminEmail');
            const passInput = document.getElementById('adminPassword');
            if (emailInput) emailInput.value = data.email;
            if (passInput) {
                passInput.value = '';
                passInput.placeholder = 'Kosongkan jika tidak ingin mengubah';
            }
        })
        .catch(e => console.error('Load admin error:', e));
}

function deleteAdmin(id) {
    if (!confirm('Yakin hapus akun ini?')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/akun/${id}`;
    form.innerHTML = `@csrf @method('DELETE')`;
    document.body.appendChild(form);
    form.submit();
}

// Fungsi untuk Konfirmasi Hapus dengan Popup (SweetAlert2)
function confirmDelete(id) {
    // 1. Tampilkan Popup Konfirmasi
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
        // 2. Jika user klik "Ya, Hapus!"
        if (result.isConfirmed) {
            // Kirim permintaan hapus (AJAX)
            fetch(`/admin/petugas/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Jika berhasil dihapus
                    Swal.fire({
                        title: 'Terhapus!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#20A726'
                    }).then(() => {
                        location.reload(); // Refresh halaman otomatis
                    });
                } else {
                    // Jika gagal
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

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ==================== MODAL PETUGAS (TAMBAH/EDIT) ====================
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
        // Mode Edit
        if (title) title.textContent = 'Edit Akun Petugas';
        if (btnSimpan) btnSimpan.textContent = 'Update';
        if (passHint) passHint.textContent = '(Kosongkan jika tidak ingin mengubah)';
        
        const petugasId = document.getElementById('petugasId');
        const namaLengkap = document.getElementById('namaLengkap');
        const emailPetugas = document.getElementById('emailPetugas');
        const noTelepon = document.getElementById('noTelepon');
        const levelPetugas = document.getElementById('levelPetugas');
        const passwordPetugas = document.getElementById('passwordPetugas');
        
        if (petugasId) petugasId.value = data.id || '';
        if (namaLengkap) namaLengkap.value = data.nama || '';
        if (emailPetugas) emailPetugas.value = data.email || '';
        if (noTelepon) noTelepon.value = data.telpon || '';
        if (levelPetugas) levelPetugas.value = data.level || '';
        if (passwordPetugas) {
            passwordPetugas.value = '';
            passwordPetugas.required = false;
        }
    } else {
        // Mode Tambah
        if (title) title.textContent = 'Tambah Akun Petugas';
        if (btnSimpan) btnSimpan.textContent = 'Simpan';
        if (passHint) passHint.textContent = '';
        
        const form = document.getElementById('formPetugas');
        if (form) form.reset();
        
        const petugasId = document.getElementById('petugasId');
        if (petugasId) petugasId.value = '';
        
        const passwordPetugas = document.getElementById('passwordPetugas');
        if (passwordPetugas) passwordPetugas.required = true;
    }
}

function closeModalPetugas() {
    const modal = document.getElementById('modalPetugas');
    if (modal) {
        modal.classList.remove('active');
    }
    document.body.style.overflow = '';
    
    const form = document.getElementById('formPetugas');
    if (form) form.reset();
}

// ==================== EVENT LISTENERS & SUBMIT HANDLER ====================
document.addEventListener('DOMContentLoaded', function() {
    // --- Event Listeners untuk Modal Petugas ---
    const btnClose = document.getElementById('btnClosePetugasModal');
    const btnBatal = document.getElementById('btnBatalPetugas');
    const modal = document.getElementById('modalPetugas');
    
    if (btnClose) btnClose.addEventListener('click', closeModalPetugas);
    if (btnBatal) btnBatal.addEventListener('click', closeModalPetugas);
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target.id === 'modalPetugas') closeModalPetugas();
        });
    }
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeModalPetugas();
        }
    });
    
    // --- Handle Submit Form Petugas (AJAX) ---
    const form = document.getElementById('formPetugas');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
        
        const petugasId = document.getElementById('petugasId');
        const isEdit = petugasId && petugasId.value !== '';
        const btnSimpan = document.getElementById('btnSimpanPetugas');
        
        if (btnSimpan) {
            btnSimpan.disabled = true;
            const originalText = btnSimpan.textContent;
            btnSimpan.textContent = isEdit ? 'Menyimpan...' : 'Menambahkan...';
        }
        
        const formData = new FormData(form);

        // --- PERBAIKAN MULAI DI SINI ---
        const baseUrl = '{{ url("admin/petugas") }}';
        let url = baseUrl;

        // Kita paksa method pengiriman selalu 'POST' agar FormData terbaca stabil
        if (isEdit) {
            url = baseUrl + '/' + encodeURIComponent(petugasId.value);
            // Method Spoofing: Laravel akan menganggap ini request PUT karena field ini
            formData.append('_method', 'PUT'); 
        }
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            const contentType = response.headers.get("content-type");
            
            if (contentType && contentType.indexOf("application/json") !== -1) {
                const result = await response.json();
                console.log('Response:', result);
                
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
                let errorMsg = result.message || 'Terjadi kesalahan';
                Swal.fire({
                    title: 'Gagal!',
                    text: errorMsg,
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
});
</script>

<script>
// 🔍 Search Akun: Filter by Nama & Email (Client-side)
document.getElementById('searchAkun')?.addEventListener('input', function(e) {
    const keyword = e.target.value.toLowerCase().trim();
    
    // Filter Cards (Akun Utama & Kedua)
    const cards = document.querySelectorAll('.account-card');
    cards.forEach(card => {
        const spans = card.querySelectorAll('span');
        let found = false;
        spans.forEach(span => {
            const text = span.textContent.toLowerCase();
            if (text.includes(keyword)) found = true;
        });
        card.style.display = found ? '' : 'none';
    });
    
    // Filter Table Rows (Petugas)
    const rows = document.querySelectorAll('.petugas-table-container tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(keyword) ? '' : 'none';
    });
});
</script>

@endpush