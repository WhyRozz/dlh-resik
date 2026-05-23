<!-- Fungsi: Menampilkan notifikasi ketika jumlah akun admin sudah mencapai batas maksimal 3 -->
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

<!-- Fungsi: Konfirmasi sebelum sistem mengirim kode OTP ke email target -->
<div class="modal" id="otpRequestModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="otpModalTitle">Kirim Kode OTP</div>
            <span class="close-modal" onclick="closeModal('otpRequestModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Kode OTP akan dikirim ke email berikut:</p>
            <!-- Fungsi: Menampilkan alamat email tujuan pengiriman OTP -->
            <div style="background:#f0f9f0; padding:10px; border-radius:6px; font-weight:bold; color:#20A726;"
                 id="targetEmailDisplay">
                email@domain.com
            </div>
            <!-- Fungsi: Memicu pengiriman kode OTP ke backend via AJAX -->
            <button type="button" class="btn-small btn-otp" onclick="sendOTPToTarget()" style="margin-top:15px;">
                Kirim Kode OTP Sekarang
            </button>
            <div id="otpRequestStatus" style="margin-top:10px;"></div>
        </div>
    </div>
</div>

<!-- Fungsi: Form input untuk memverifikasi kode OTP yang telah dikirim -->
<div class="modal" id="otpVerifyModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">Masukkan Kode OTP</div>
            <span class="close-modal" onclick="closeModal('otpVerifyModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 15px; line-height: 1.6;">
                Masukkan kode 4 digit yang dikirim ke<br>
                <span id="otpTargetEmail" style="display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 6px 16px; border-radius: 20px; font-weight: 600; margin: 8px 0; word-break: break-all;">email@domain.com</span>
            </p>
            <label for="otpInput" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Kode OTP</label>
            <!-- Fungsi: Input kode OTP dengan filter hanya angka dan maksimal 4 digit -->
            <input type="text" id="otpInput" maxlength="4" placeholder="----"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                   style="width: 100%; padding: 16px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 28px; font-weight: 700; text-align: center; letter-spacing: 12px; color: #2d6a4f; background: #fafafa; font-family: 'Courier New', monospace; box-sizing: border-box;">
            <div id="otpVerifyStatus" style="margin-top: 10px;"></div>
        </div>
        <div class="modal-footer" style="display: flex; gap: 10px; padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #e9ecef;">
            <button type="button" class="btn-cancel" onclick="closeModal('otpVerifyModal')" 
                    style="flex: 1; padding: 12px; background: #e9ecef; color: #495057; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Batal
            </button>
            <button type="button" class="btn-otp" onclick="verifyOTP()"
                    style="flex: 1; padding: 12px; background: #20A726; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Verifikasi
            </button>
        </div>
    </div>
</div>

<!-- ✅ BARU DITAMBAHKAN: Form Edit Akun Admin (Wajib untuk OTP flow) -->
<div id="formSection" style="display: none; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin: 20px 0;">
    <h3 id="formTitle" style="color: #20A726; margin-bottom: 15px; font-size: 18px; font-weight: 600;">Edit Akun Admin</h3>
    
    <form id="accountForm" method="POST" action="">
        @csrf
        @method('PUT')
        
        <!-- Hidden ID Admin -->
        <input type="hidden" id="formIdAdmin" name="id_admin">
        
        <!-- Email Field -->
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600; color: #333; font-size: 14px;">Email</label>
            <input type="email" id="email" name="email" 
                   class="form-control" 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
                   placeholder="contoh@email.com" required>
        </div>
        
        <!-- Password Field -->
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="password" style="display: block; margin-bottom: 5px; font-weight: 600; color: #333; font-size: 14px;">
                Kata Sandi <span id="passHintAdmin" style="font-weight:400; color:#6c757d; font-size:12px;">(Kosongkan jika tidak ingin mengubah)</span>
            </label>
            <div style="position: relative;">
                <input type="password" id="password" name="password" 
                       class="form-control" 
                       style="width: 100%; padding: 10px 40px 10px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
                       placeholder="••••••••">
                <button type="button" id="togglePasswordAdmin" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center;">
                    <img id="eyeIconAdmin" src="{{ asset('assets/icons/hide.png') }}" alt="Toggle" style="width: 18px; height: 18px;">
                </button>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
            <button type="button" id="btnBatalAdmin" 
                    style="flex: 0 0 150px; max-width: 200px; padding: 10px; background: #e9ecef; color: #495057; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">
                Batal
            </button>
            <button type="submit" id="btnSimpanAdmin" 
                    style="flex: 0 0 150px; max-width: 200px; padding: 10px; background: #20A726; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
<!-- ✅ END Form Edit Akun Admin -->


<!-- Fungsi: Modal form untuk menambah atau mengedit data akun petugas -->
<div class="modal-overlay" id="modalPetugas">
    <div class="modal-container">
        <!-- Header -->
        <div class="modal-header">
            <!-- Fungsi: Menampilkan judul dinamis (Tambah/Edit) sesuai konteks operasi -->
            <h3 id="modalPetugasTitle">Tambah Akun Petugas</h3>
            <button type="button" class="modal-close" id="btnClosePetugasModal">&times;</button>
        </div>

        <!-- Form Body -->
        <form id="formPetugas" class="modal-body">
            <!-- Fungsi: Penanda apakah form dalam mode tambah (kosong) atau edit (terisi ID) -->
            <input type="hidden" id="petugasId" name="id">
            <!-- Fungsi: Token keamanan Laravel untuk mencegah serangan CSRF -->
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
                <!-- Fungsi: Dropdown pilihan role petugas yang dikelompokkan berdasarkan wilayah -->
                <select id="levelPetugas" name="level" required>
                    <option value="">-- Pilih Petugas --</option>
                    <option value="petugas_dlh">Petugas DLH</option>
                    <optgroup label="--- Bank Sampah ---">
                        <option value="bank_sampah_kelurahan_kauman_kauman_nganjuk">Bank Sampah KELURAHAN KAUMAN (Kauman, Nganjuk)</option>
                        <option value="bank_sampah_kramat_bersih_kramat_nganjuk">Bank Sampah KRAMAT BERSIH (Kramat, Nganjuk)</option>
                        <option value="bank_sampah_kelurahan_cangkringan_cangkringan_nganjuk">Bank Sampah KELURAHAN CANGKRINGAN (Cangkringan, Nganjuk)</option>
                        <option value="bank_sampah_ngudi_sariro_jatirejo_nganjuk">Bank Sampah NGUDI SARIRO (Jatirejo, Nganjuk)</option>
                        <option value="bank_sampah_margo_utomo_begadung_nganjuk">Bank Sampah MARGO UTOMO (Begadung, Nganjuk)</option>
                        <option value="bank_sampah_sejahtera_kartoharjo_nganjuk">Bank Sampah SEJAHTERA (Kartoharjo, Nganjuk)</option>
                        <option value="bank_sampah_melati_kedungdowo_nganjuk">Bank Sampah MELATI (Kedungdowo, Nganjuk)</option>
                        <option value="bank_sampah_anggrek_werungotok_nganjuk">Bank Sampah ANGGREK (Werungotok, Nganjuk)</option>
                        <option value="bank_sampah_sumber_rejeki_werungotok_nganjuk">Bank Sampah SUMBER REJEKI (Werungotok, Nganjuk)</option>
                        <option value="bank_sampah_beringin_hijau_ringinanom_nganjuk">Bank Sampah BERINGIN HIJAU (Ringinanom, Nganjuk)</option>
                        <option value="bank_sampah_ploso_ploso_nganjuk">Bank Sampah PLOSO (Ploso, Nganjuk)</option>
                        <option value="bank_sampah_mulyo_agung_kudu_kertosono">Bank Sampah MULYO AGUNG (Kudu, Kertosono)</option>
                        <option value="bank_sampah_estu_sae_petak_bagor">Bank Sampah ESTU SAE (Petak, Bagor)</option>
                        <option value="bank_sampah_desa_ngangkatan_ngangkatan_rejoso">Bank Sampah DESA NGANGKATAN (Ngangkatan, Rejoso)</option>
                        <option value="bank_sampah_desa_jegreg_jegreg_lengkong">Bank Sampah DESA JEGREG (Jegreg, Lengkong)</option>
                        <option value="bank_sampah_musirkidul_musirkidul_rejoso">Bank Sampah MUSIRKIDUL (Musirkidul, Rejoso)</option>
                        <option value="bank_sampah_tanjung_tanjunganom_tanjunganom">Bank Sampah TANJUNG (Tanjunganom, Tanjunganom)</option>
                        <option value="bank_sampah_flamboyan_loceret_loceret">Bank Sampah FLAMBOYAN (Loceret, Loceret)</option>
                        <option value="bank_sampah_pelita_bogo_nganjuk">Bank Sampah PELITA (Bogo, Nganjuk)</option>
                        <option value="bank_sampah_desa_getas_getas_tanjunganom">Bank Sampah DESA GETAS (Getas, Tanjunganom)</option>
                        <option value="bank_sampah_mbejaji_juwet_ngronggot">Bank Sampah MBEJAJI (Juwet, Ngronggot)</option>
                        <option value="bank_sampah_kedondong_kedondong_bagor">Bank Sampah KEDONDONG (Kedondong, Bagor)</option>
                        <option value="bank_sampah_sinar_terang_jampes_pace">Bank Sampah SINAR TERANG (Jampes, Pace)</option>
                        <option value="bank_sampah_desa_blongko_blongko_ngetos">Bank Sampah DESA BLONGKO (Blongko, Ngetos)</option>
                        <option value="bank_sampah_bukur_bukur_patianrowo">Bank Sampah BUKUR (Bukur, Patianrowo)</option>
                        <option value="bank_sampah_bungur_makmur_bungur_sukomoro">Bank Sampah BUNGUR MAKMUR (Bungur, Sukomoro)</option>
                        <option value="bank_sampah_seger_waras_mabung_baron">Bank Sampah SEGER WARAS (Mabung, Baron)</option>
                        <option value="bank_sampah_maju_bahagia_gondanglegi_prambon">Bank Sampah MAJU BAHAGIA (Gondanglegi, Prambon)</option>
                        <option value="bank_sampah_barokah_kemlokolegi_baron">Bank Sampah BAROKAH (Kemlokolegi, Baron)</option>
                        <option value="bank_sampah_dahlia_senjayan_gondang">Bank Sampah DAHLIA (Senjayan, Gondang)</option>
                        <option value="bank_sampah_cengkok_cengkok_ngronggot">Bank Sampah CENGKOK (Cengkok, Ngronggot)</option>
                        <option value="bank_sampah_induk_salepok_omahe_nganjuk_kedondong_bagor">Bank Sampah Induk SALEPOK OMAHE NGANJUK (Kedondong, Bagor)</option>
                    </optgroup>
                </select>
            </div>

            <div class="form-group">
                <!-- Fungsi: Input kata sandi, wajib saat tambah baru dan opsional saat edit -->
                <label for="passwordPetugas">Kata Sandi <span id="passHint" style="font-weight:400; color:#6c757d; font-size:12px;"></span></label>
                <input type="password" id="passwordPetugas" name="password" placeholder="••••••••">
            </div>
        </form>

        <!-- Footer -->
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="btnBatalPetugas">Batal</button>
            <!-- Fungsi: Submit form untuk menyimpan data ke backend -->
            <button type="submit" form="formPetugas" class="btn-primary" id="btnSimpanPetugas">Simpan</button>
        </div>
    </div>
</div>