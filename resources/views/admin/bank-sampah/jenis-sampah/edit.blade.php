@extends('layouts.admin')

@section('title', 'Edit Jenis Sampah - RESIK')

@push('styles')
    {{-- FUNGSI: Memuat file CSS khusus untuk halaman jenis sampah --}}
    <link rel="stylesheet" href="{{ asset('css/jenis-sampah.css?v=' . time()) }}">
@endpush

@section('content')
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-edit"></i> Edit Jenis Sampah</h2>
    </div>

    <form action="{{ route('admin.bank-sampah.jenis-sampah.update', $jenisSampah->id_jenis_sampah) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Upload Gambar -->
        <div class="form-group full-width">
            <label>Upload Foto Jenis Sampah</label>
            
            {{-- ✅ KOTAK UPLOAD DENGAN PREVIEW DI DALAMNYA --}}
            <div class="image-upload" id="upload-area" style="cursor: pointer; position: relative;">
                <i class="fas fa-cloud-upload-alt"></i>
                <p id="upload-text">Klik untuk upload foto baru</p>
                <small>Format: JPG, PNG. Max: 2MB</small>
                
                {{-- ✅ PREVIEW GAMBAR (Di dalam kotak) --}}
                <div id="image-preview" style="margin-top: 5px; display: flex; justify-content: center;">
                    @if($jenisSampah->gambar)
                        @php
                            $gambarPath = str_replace(['storage/', 'uploads/'], '', $jenisSampah->gambar);
                        @endphp
                        <img src="{{ asset('uploads/' . $gambarPath) }}" 
                             alt="{{ $jenisSampah->jenis }}"
                             id="current-image"
                             style="max-width: 180px; height: auto; border-radius: 8px; display: block;">
                    @endif
                </div>
            </div>
            
            <input type="file" id="gambar" name="gambar" accept="image/*" style="display: none;">
            @error('gambar')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <!-- Jenis Sampah -->
        <div class="form-group full-width">
            <label>Jenis Sampah <span class="required">*</span></label>
            <input type="text" name="jenis" value="{{ old('jenis', $jenisSampah->jenis) }}" placeholder="Contoh: Plastik, Kertas, Logam" required>
            @error('jenis')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <!-- Satuan & Harga -->
        <div class="form-row">
            <div class="form-group">
                <label>Satuan <span class="required">*</span></label>
                <select name="satuan" required>
                    <option value="">Pilih Satuan</option>
                    <option value="Kg" {{ old('satuan', $jenisSampah->satuan) == 'Kg' ? 'selected' : '' }}>Kg (Kilogram)</option>
                    <option value="Lt" {{ old('satuan', $jenisSampah->satuan) == 'Lt' ? 'selected' : '' }}>Lt (Liter)</option>
                </select>
                @error('satuan')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Harga (Rp) <span class="required">*</span></label>
                <input type="number" name="harga" value="{{ old('harga', $jenisSampah->harga) }}" placeholder="Contoh: 2000" min="0" required>
                @error('harga')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-actions">
            <a href="{{ route('admin.bank-sampah.jenis-sampah.index') }}" class="btn btn-cancel">
                Batal
            </a>
            <button type="submit" class="btn btn-submit">
                <i class="fas fa-edit"></i> Update
            </button>
        </div>
    </form>
</div>

@push('scripts')
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- FUNGSI: Memuat file JS eksternal yang berisi semua fungsi form handling --}}
    <script src="{{ asset('js/jenis-sampah.js?v=' . time()) }}"></script>
    
{{-- Script untuk preview image saat edit --}}
<script>
    document.getElementById('gambar').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        const uploadText = document.getElementById('upload-text');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Ganti gambar yang ada dengan preview baru
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; height: auto; border-radius: 8px;">`;
                
                if (uploadText) {
                    uploadText.textContent = 'Klik untuk ganti foto';
                }
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>

{{-- SweetAlert untuk notifikasi --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false,
        position: 'center'
    });
</script>
@endif
@endpush
@endsection