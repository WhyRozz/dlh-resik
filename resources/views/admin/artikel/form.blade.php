@extends('layouts.admin')

<!-- Fungsi: Menetapkan judul halaman dinamis berdasarkan mode (Edit/Tambah) -->
@section('title', isset($artikel) ? 'Edit Artikel' : 'Tambah Artikel')
@section('page-title', isset($artikel) ? 'Edit Artikel' : 'Tambah Artikel')
@section('page-title-mobile', isset($artikel) ? 'EDIT' : 'TAMBAH')

@push('styles')
<!-- Fungsi: Memuat file CSS khusus untuk halaman form artikel -->
<link rel="stylesheet" href="{{ asset('css/artikel-form.css') }}">
@endpush

@section('content')
<!-- Fungsi: Container utama form artikel -->
<div class="form-container">
    <!-- Fungsi: Judul form yang berubah dinamis: "Edit Artikel" atau "Tambah Artikel" -->
    <div class="form-title">{{ isset($artikel) ? 'Edit' : 'Tambah' }} Artikel</div>

    <!-- Fungsi: Form utama dengan action dinamis berdasarkan mode dan enctype untuk upload file -->
    <form id="artikelForm"
          method="POST"
          action="{{ isset($artikel) ? route('admin.artikel.update', $artikel->id_artikel) : route('admin.artikel.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($artikel))
            <!-- Fungsi: Method spoofing PUT untuk update data di Laravel -->
            @method('PUT')
        @endif

        <div class="form-row-main">
            <!-- Fungsi: Section upload foto artikel -->
            <div class="upload-section">
                <label class="upload-label">Upload Foto</label>
                <!-- Fungsi: Area klik untuk trigger input file -->
                <div class="upload-area" id="uploadArea">
                    <!-- Fungsi: Placeholder yang tampil ketika belum ada gambar dipilih -->
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <div class="upload-text">Klik untuk upload foto artikel</div>
                        <div class="upload-hint">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
                    </div>
                    <!-- Fungsi: Preview gambar yang tampil setelah file dipilih atau saat mode edit -->
                    <div class="upload-preview {{ isset($artikel) && $artikel->foto ? 'show' : '' }}" id="uploadPreview">
                        <img id="previewImage" 
                             src="{{ isset($artikel) && $artikel->foto ? asset('storage/' . $artikel->foto) : '' }}" 
                             alt="Preview foto artikel">
                        <!-- Fungsi: Tombol untuk menghapus gambar yang sudah dipilih -->
                        <button type="button" class="remove-image" onclick="removeImage()" title="Hapus gambar">×</button>
                    </div>
                    <!-- Fungsi: Input file tersembunyi yang di-trigger via klik area upload -->
                    <input type="file" 
                           id="fotoInput" 
                           name="foto" 
                           accept="image/jpeg,image/png,image/gif"
                           style="display: none;">
                </div>
            </div>

            <!-- Fungsi: Section input form data artikel -->
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label" for="judul">Judul Artikel *</label>
                    <!-- Fungsi: Input judul dengan validasi required, maxlength, dan old() untuk retain value -->
                    <input type="text"
                           class="form-input"
                           id="judul"
                           name="judul"
                           value="{{ old('judul', $artikel->judul ?? '') }}"
                           placeholder="Masukkan judul artikel"
                           maxlength="255"
                           required>
                    @error('judul')
                    <!-- Fungsi: Menampilkan error validation untuk field judul -->
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tanggal">Tanggal Publikasi *</label>
                    <!-- Fungsi: Input datetime-local dengan format value yang disesuaikan untuk HTML5 -->
                    <input type="datetime-local"
                           class="form-input"
                           id="tanggal"
                           name="tanggal"
                           value="{{ old('tanggal', isset($artikel) && $artikel->tanggal ? $artikel->tanggal->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}"
                           required>
                    @error('tanggal')
                    <!-- Fungsi: Menampilkan error validation untuk field tanggal -->
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="deskripsi">Deskripsi Artikel *</label>
                    <!-- Fungsi: Textarea untuk konten artikel dengan validasi required -->
                    <textarea class="form-textarea"
                              id="deskripsi"
                              name="deskripsi"
                              placeholder="Tulis konten artikel di sini..."
                              required>{{ old('deskripsi', $artikel->deskripsi ?? '') }}</textarea>
                    @error('deskripsi')
                    <!-- Fungsi: Menampilkan error validation untuk field deskripsi -->
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Fungsi: Section tombol aksi form (Batal & Submit) -->
        <div class="form-actions">
            <a href="{{ route('admin.artikel.index') }}" class="btn btn-batal">Batal</a>
            <!-- Fungsi: Tombol submit dengan teks dinamis berdasarkan mode -->
            <button type="submit" class="btn btn-primary" id="submitBtn">
                {{ isset($artikel) ? 'Perbarui Artikel' : 'Tambah Artikel' }}
            </button>
        </div>
    </form>
</div>

<!-- Fungsi: Modal overlay untuk menampilkan error validation dari server -->
<div id="errorModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header error">
            <h3>Kesalahan!</h3>
        </div>
        <div class="modal-body">
            <p id="errorMessage">Terjadi kesalahan.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-batal" onclick="hideErrorModal()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
<!-- Fungsi: Deklarasi variabel untuk elemen-elemen upload handling -->
const uploadArea = document.getElementById('uploadArea');
const fotoInput = document.getElementById('fotoInput');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const uploadPreview = document.getElementById('uploadPreview');
const previewImage = document.getElementById('previewImage');

<!-- Fungsi: Event listener untuk trigger file input ketika area upload diklik -->
uploadArea.addEventListener('click', function(e) {
    if (e.target !== uploadPreview && !uploadPreview.contains(e.target) && 
        !e.target.classList.contains('remove-image')) {
        fotoInput.click();
    }
});

<!-- Fungsi: Event listener untuk handle file selection dan validasi -->
fotoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        <!-- Fungsi: Validasi ukuran file maksimal 2MB -->
        if (file.size > 2 * 1024 * 1024) {
            showError('Ukuran gambar maksimal 2MB');
            fotoInput.value = '';
            return;
        }
        
        <!-- Fungsi: Validasi tipe file hanya JPG, PNG, GIF -->
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            showError('Format gambar harus JPG, JPEG, atau PNG');
            fotoInput.value = '';
            return;
        }
        
        <!-- Fungsi: Preview gambar menggunakan FileReader -->
        const reader = new FileReader();
        reader.onload = function(e) {
            showPreview(e.target.result);
        };
        reader.readAsDataURL(file);
    }
});

<!-- Fungsi: Menampilkan preview gambar dan menyembunyikan placeholder -->
function showPreview(src) {
    uploadPlaceholder.style.display = 'none';
    uploadPreview.classList.add('show');
    previewImage.src = src;
}

<!-- Fungsi: Menghapus gambar yang dipilih dan reset ke state placeholder -->
function removeImage() {
    fotoInput.value = '';
    uploadPlaceholder.style.display = 'flex';
    uploadPreview.classList.remove('show');
    previewImage.src = '';
}

<!-- Fungsi: Menampilkan modal error dengan pesan yang diberikan -->
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.add('show');
}

<!-- Fungsi: Menyembunyikan modal error -->
function hideErrorModal() {
    document.getElementById('errorModal').classList.remove('show');
}

<!-- Fungsi: Disable tombol submit dan ubah teks saat form sedang disubmit -->
const form = document.getElementById('artikelForm');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', function(e) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
});

<!-- Fungsi: Close modal ketika user klik di luar area modal content -->
document.getElementById('errorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideErrorModal();
    }
});

<!-- Fungsi: Show validation errors dari server di modal ketika ada error -->
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    const errors = @json($errors->all());
    showError(errors[0]);
});
@endif

<!-- Fungsi: Tampilkan preview gambar existing ketika mode edit dan ada foto -->
@if(isset($artikel) && $artikel->foto)
document.addEventListener('DOMContentLoaded', function() {
    showPreview('{{ asset('storage/' . $artikel->foto) }}');
});
@endif

<!-- Fungsi: Redirect ke index dengan pesan success setelah submit berhasil -->
@if(session('success'))
window.location.href = "{{ route('admin.artikel.index') }}";
@endif
</script>
@endpush