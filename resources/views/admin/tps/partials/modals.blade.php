{{-- resources/views/admin/tps/partials/modals.blade.php --}}

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

<script>
let tpsIdToDelete = null;

function konfirmasiHapus(id) {
    tpsIdToDelete = id;
    const popup = document.getElementById('confirmPopup');
    popup.classList.add('active');
    setTimeout(() => {
        popup.querySelector('.popup-content').classList.add('show');
    }, 10);
}

function closeConfirmPopup() {
    const popup = document.getElementById('confirmPopup');
    popup.querySelector('.popup-content').classList.remove('show');
    setTimeout(() => {
        popup.classList.remove('active');
        tpsIdToDelete = null;
    }, 300);
}

function showSuccessPopup(message) {
    document.getElementById('successMessage').textContent = message;
    const popup = document.getElementById('successPopup');
    popup.classList.add('active');
    setTimeout(() => {
        popup.querySelector('.popup-content').classList.add('show');
    }, 10);
}

function closeSuccessPopup() {
    const popup = document.getElementById('successPopup');
    popup.querySelector('.popup-content').classList.remove('show');
    setTimeout(() => {
        popup.classList.remove('active');
    }, 300);
}

// Auto show success message from session
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showSuccessPopup("{{ session('success') }}");
    @endif
});

// Close popup when clicking outside
document.querySelectorAll('.popup-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            this.querySelector('.popup-content')?.classList.remove('show');
        }
    });
});

// Delete button handler
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!tpsIdToDelete) return;
    
    this.disabled = true;
    this.textContent = 'Menghapus...';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/tps/${tpsIdToDelete}`;
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    document.body.appendChild(form);
    form.submit();
});
</script>