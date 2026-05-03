{{-- MODAL: Batas Akun --}}
<div class="modal" id="limitModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">⚠️ Batas Akun Tercapai</div>
            <span class="close-modal" onclick="closeModal('limitModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p><strong>Jumlah akun admin sudah mencapai batas maksimal (3).</strong></p>
            <p>Silakan <strong>hapus salah satu akun tambahan</strong> terlebih dahulu jika ingin menambah akun baru.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-small btn-otp" onclick="closeModal('limitModal')">Mengerti</button>
        </div>
    </div>
</div>

{{-- MODAL: Kirim OTP --}}
<div class="modal" id="otpRequestModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="otpModalTitle">Kirim Kode OTP</div>
            <span class="close-modal" onclick="closeModal('otpRequestModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Kode OTP akan dikirim ke email berikut:</p>
            <div style="background:#f0f9f0; padding:10px; border-radius:6px; font-weight:bold; color:#20A726;"
                 id="targetEmailDisplay">
                email@domain.com
            </div>
            <button type="button" class="btn-small btn-otp" onclick="sendOTPToTarget()" style="margin-top:15px;">
                Kirim Kode OTP Sekarang
            </button>
            <div id="otpRequestStatus" style="margin-top:10px;"></div>
        </div>
    </div>
</div>

{{-- MODAL: Verifikasi OTP --}}
<div class="modal" id="otpVerifyModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">Masukkan Kode OTP</div>
            <span class="close-modal" onclick="closeModal('otpVerifyModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Masukkan kode 4 digit yang dikirim ke <span id="otpTargetEmail" style="font-weight:bold;">email@domain.com</span>.</p>
            <label for="otpInput">Kode OTP</label>
            <input type="text" id="otpInput" maxlength="4" placeholder="1234"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            <div id="otpVerifyStatus"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-small btn-cancel" onclick="closeModal('otpVerifyModal')">Batal</button>
            <button type="button" class="btn-small btn-otp" onclick="verifyOTP()">Verifikasi</button>
        </div>
    </div>
</div>



{{-- =================================================================== --}}
{{-- MODAL: Tambah/Edit Akun Petugas (Popup Style Penjemputan)          --}}
{{-- =================================================================== --}}
<div class="modal-overlay" id="modalPetugas">
    <div class="modal-container">
        <!-- Header -->
        <div class="modal-header">
            <h3 id="modalPetugasTitle">Tambah Akun Petugas</h3>
            <button type="button" class="modal-close" id="btnClosePetugasModal">&times;</button>
        </div>

        <!-- Form Body -->
        <form id="formPetugas" class="modal-body">
            <input type="hidden" id="petugasId" name="id">
            @csrf
            
            <div class="form-group">
                <label for="namaLengkap">Nama Admin</label>
                <input type="text" id="namaLengkap" name="nama_lengkap" placeholder="Masukkan nama admin" required>
            </div>

            <div class="form-group">
                <label for="emailPetugas">Email</label>
                <input type="email" id="emailPetugas" name="email" placeholder="contoh@email.com" required>
            </div>

            <div class="form-group">
                <label for="noTelepon">No Telpon</label>
                <input type="tel" id="noTelepon" name="no_telepon" placeholder="08xxxxxxxxxx" required>
            </div>

            <div class="form-group">
                <label for="levelPetugas">Petugas</label>
                <select id="levelPetugas" name="level" required>
                    <option value="">-- Pilih Petugas --</option>
                    <option value="petugas_dlh">Petugas DLH</option>
                    <option value="bank_sampah">Bank Sampah</option>
                </select>
            </div>

            <div class="form-group">
                <label for="passwordPetugas">Kata Sandi <span id="passHint" style="font-weight:400; color:#6c757d; font-size:12px;"></span></label>
                <input type="password" id="passwordPetugas" name="password" placeholder="••••••••">
            </div>
        </form>

        <!-- Footer -->
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="btnBatalPetugas">Batal</button>
            <button type="submit" form="formPetugas" class="btn-primary" id="btnSimpanPetugas">Simpan</button>
        </div>
    </div>
</div>
