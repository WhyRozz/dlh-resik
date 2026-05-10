<!-- Fungsi: Popup overlay untuk konfirmasi hapus artikel -->
<div id="confirmPopup" class="popup-overlay">
    <div class="popup-content">
        <h3>Konfirmasi Hapus</h3>
        <p>Apakah Anda yakin ingin menghapus artikel ini?</p>
        <div class="popup-btns">
            <!-- Fungsi: Tombol Batal untuk menutup popup konfirmasi -->
            <button type="button" class="popup-btn cancel" onclick="closeConfirmPopup()">Batal</button>
            <!-- Fungsi: Tombol Konfirmasi untuk mengeksekusi fungsi hapusArtikel() -->
            <button type="button" class="popup-btn confirm" onclick="hapusArtikel()">Ya, Hapus</button>
        </div>
    </div>
</div>

<!-- Fungsi: Popup overlay untuk menampilkan notifikasi sistem -->
<div id="notificationPopup" class="popup-overlay">
    <div class="popup-content">
        <!-- Fungsi: Judul notifikasi yang dinamis via JavaScript -->
        <h3 id="notificationTitle">Notifikasi</h3>
        <!-- Fungsi: Pesan notifikasi yang diisi secara dinamis via JavaScript -->
        <p id="notificationMessage"></p>
        <div style="margin-top: 15px;">
            <!-- Fungsi: Tombol untuk menutup popup notifikasi -->
            <button type="button" class="popup-btn" onclick="closeNotificationPopup()">Tutup</button>
        </div>
    </div>
</div>