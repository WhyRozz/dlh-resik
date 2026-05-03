@extends('layouts.admin')

@section('title', isset($artikel) ? 'Edit Artikel' : 'Tambah Artikel')
@section('page-title', isset($artikel) ? 'Edit Artikel' : 'Tambah Artikel')
@section('page-title-mobile', isset($artikel) ? 'EDIT' : 'TAMBAH')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/artikel-form.css') }}">
@endpush

@section('content')
<div class="form-container">
    <div class="form-title">{{ isset($artikel) ? 'Edit' : 'Tambah' }} Artikel</div>

    <form id="artikelForm"
          method="POST"
          action="{{ isset($artikel) ? route('admin.artikel.update', $artikel->id_artikel) : route('admin.artikel.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if(isset($artikel))
            @method('PUT')
        @endif

        <div class="form-row-main">
            <!-- Upload Foto -->
            <div class="upload-section">
                <label class="upload-label">Upload Foto</label>
                <div class="upload-area" id="uploadArea">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <div class="upload-text">Klik untuk upload foto artikel</div>
                        <div class="upload-hint">Format: JPG, JPEG, PNG. Maksimal 2MB.</div>
                    </div>
                    <div class="upload-preview" id="uploadPreview">
                        <img id="previewImage" src="" alt="Preview foto artikel">
                        <button type="button" class="remove-image" onclick="removeImage()" title="Hapus gambar">×</button>
                    </div>
                    <input type="file" 
                           id="fotoInput" 
                           name="foto" 
                           accept="image/jpeg,image/png,image/gif"
                           style="display: none;">
                </div>
            </div>

            <!-- Form Fields -->
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label" for="judul">Judul Artikel *</label>
                    <input type="text"
                           class="form-input"
                           id="judul"
                           name="judul"
                           value="{{ old('judul', $artikel->judul ?? '') }}"
                           placeholder="Masukkan judul artikel"
                           maxlength="255"
                           required>
                    @error('judul')
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tanggal">Tanggal Publikasi *</label>
                    <input type="datetime-local"
                           class="form-input"
                           id="tanggal"
                           name="tanggal"
                           value="{{ old('tanggal', isset($artikel) && $artikel->tanggal ? $artikel->tanggal->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}"
                           required>
                    @error('tanggal')
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="deskripsi">Deskripsi Artikel *</label>
                    <textarea class="form-textarea"
                              id="deskripsi"
                              name="deskripsi"
                              placeholder="Tulis konten artikel di sini..."
                              required>{{ old('deskripsi', $artikel->deskripsi ?? '') }}</textarea>
                    @error('deskripsi')
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.artikel.index') }}" class="btn btn-batal">Batal</a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                {{ isset($artikel) ? 'Perbarui Artikel' : 'Tambah Artikel' }}
            </button>
        </div>
    </form>
</div>

<!-- Error Modal -->
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
// Upload handling
const uploadArea = document.getElementById('uploadArea');
const fotoInput = document.getElementById('fotoInput');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const uploadPreview = document.getElementById('uploadPreview');
const previewImage = document.getElementById('previewImage');

// Click upload area to trigger file input
uploadArea.addEventListener('click', function(e) {
    if (e.target !== uploadPreview && !uploadPreview.contains(e.target) && 
        !e.target.classList.contains('remove-image')) {
        fotoInput.click();
    }
});

// Handle file selection
fotoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            showError('Ukuran gambar maksimal 2MB');
            fotoInput.value = '';
            return;
        }
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            showError('Format gambar harus JPG, JPEG, atau PNG');
            fotoInput.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            showPreview(e.target.result);
        };
        reader.readAsDataURL(file);
    }
});

function showPreview(src) {
    uploadPlaceholder.style.display = 'none';
    uploadPreview.classList.add('show');
    previewImage.src = src;
}

function removeImage() {
    fotoInput.value = '';
    uploadPlaceholder.style.display = 'flex';
    uploadPreview.classList.remove('show');
    previewImage.src = '';
}

// Error handling
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.add('show');
}

function hideErrorModal() {
    document.getElementById('errorModal').classList.remove('show');
}

// Form submission handling
const form = document.getElementById('artikelForm');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', function(e) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
});

// Close modal when clicking outside
document.getElementById('errorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideErrorModal();
    }
});

// Show validation errors from server
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    const errors = @json($errors->all());
    showError(errors[0]);
});
@endif

// Show existing image if editing
@if(isset($artikel) && $artikel->foto)
document.addEventListener('DOMContentLoaded', function() {
    showPreview('{{ asset('storage/' . $artikel->foto) }}');
});
@endif

// Redirect dengan success message setelah submit
@if(session('success'))
window.location.href = "{{ route('admin.artikel.index') }}";
@endif
</script>
@endpush