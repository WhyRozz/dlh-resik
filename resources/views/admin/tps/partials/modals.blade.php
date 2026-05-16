{{-- resources/views/admin/tps/partials/modals.blade.php --}}
{{-- FUNGSI: Struktur HTML untuk popup modal konfirmasi & notifikasi --}}

{{-- Popup Konfirmasi Hapus --}}
<div id="confirmPopup" class="popup-overlay">
    <div class="popup-content">
        <h3>Konfirmasi Hapus</h3>
        <p>Apakah Anda yakin ingin menghapus data TPS ini?</p>
        <div class="popup-btns">
            <button type="button" class="popup-btn cancel" onclick="closeConfirmPopup()">Batal</button>
            <button type="button" class="popup-btn confirm" id="confirmDeleteBtn">Ya, Hapus</button>
        </div>
    </div>
</div>

{{-- Popup Notifikasi Sukses --}}
<div id="successPopup" class="popup-overlay">
    <div class="popup-content success">
        <h3>Berhasil!</h3>
        <p id="successMessage">Operasi berhasil.</p>
        <button type="button" class="popup-btn" style="background: #28a745;" onclick="closeSuccessPopup()">Tutup</button>
    </div>
</div>

{{-- 🔗 BRIDGE: Pass session success message ke file JS eksternal --}}
@if(session('success'))
<script>
    window.TpsConfig = {
        successMessage: "{{ session('success') }}"
    };
</script>
@endif