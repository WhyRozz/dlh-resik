{{-- Popup Konfirmasi Hapus --}}
<div id="confirmPopup" class="popup-overlay">
    <div class="popup-content">
        <h3>Konfirmasi Hapus</h3>
        <p>Apakah Anda yakin ingin menghapus artikel ini?</p>
        <div class="popup-btns">
            <button type="button" class="popup-btn cancel" onclick="closeConfirmPopup()">Batal</button>
            <button type="button" class="popup-btn confirm" onclick="hapusArtikel()">Ya, Hapus</button>
        </div>
    </div>
</div>

{{-- Popup Notifikasi --}}
<div id="notificationPopup" class="popup-overlay">
    <div class="popup-content">
        <h3 id="notificationTitle">Notifikasi</h3>
        <p id="notificationMessage"></p>
        <div style="margin-top: 15px;">
            <button type="button" class="popup-btn" onclick="closeNotificationPopup()">Tutup</button>
        </div>
    </div>
</div>
