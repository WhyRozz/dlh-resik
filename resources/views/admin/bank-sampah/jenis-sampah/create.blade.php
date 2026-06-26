@extends('layouts.admin')

@section('title', 'Tambah Jenis Sampah - RESIK')

@push('styles')
    {{-- FUNGSI: Memuat file CSS khusus untuk halaman jenis sampah --}}
    <link rel="stylesheet" href="{{ asset('css/jenis-sampah.css?v=' . time()) }}">
@endpush

@section('content')
    @php
        // ✅ Redirect jika bukan Super Admin
        if (!auth()->guard('admin')->user()->isSuperAdmin()) {
            return redirect()
                ->route('admin.bank-sampah.jenis-sampah.index')
                ->with('error', '❌ Anda tidak memiliki izin untuk mengakses halaman ini!');
        }
    @endphp

    <div class="form-container">
        <div class="form-header">
            <h2><i class="fas fa-plus"></i> Tambah Jenis Sampah</h2>
        </div>

        <form action="{{ route('admin.bank-sampah.jenis-sampah.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Upload Gambar -->
            <div class="form-group full-width">
                <label>Upload Foto Jenis Sampah</label>
                <div class="image-upload" onclick="document.getElementById('gambar').click()" style="cursor: pointer;">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Klik untuk upload foto</p>
                    <small>Format: JPG, PNG. Max: 2MB</small>
                    <div class="image-preview" id="imagePreview"></div>
                </div>
                <input type="file" id="gambar" name="gambar" accept="image/*" style="display: none;">
                @error('gambar')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <!-- Jenis Sampah -->
            <div class="form-group full-width">
                <label>Jenis Sampah <span class="required">*</span></label>
                <input type="text" name="jenis" value="{{ old('jenis') }}"
                    placeholder="Contoh: Plastik, Kertas, Logam" required>
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
                        <option value="Kg" {{ old('satuan') == 'Kg' ? 'selected' : '' }}>Kg (Kilogram)</option>
                        <option value="Lt" {{ old('satuan') == 'Lt' ? 'selected' : '' }}>Lt (Liter)</option>
                    </select>
                    @error('satuan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Harga (Rp) <span class="required">*</span></label>
                    <input type="number" name="harga" value="{{ old('harga') }}" placeholder="Contoh: 2000"
                        min="0" required>
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
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        {{-- SweetAlert2 CDN --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        {{-- FUNGSI: Memuat file JS eksternal yang berisi semua fungsi form handling --}}
        <script src="{{ asset('js/jenis-sampah.js?v=' . time()) }}"></script>

        {{-- Script untuk preview image --}}
        <script>
            document.getElementById('gambar').addEventListener('change', function(e) {
                const preview = document.getElementById('imagePreview');
                const uploadText = document.querySelector('.image-upload p');

                if (this.files && this.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        if (preview) {
                            preview.innerHTML =
                                `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; height: auto; border-radius: 8px;">`;
                            preview.style.display = 'block';

                            if (uploadText) {
                                uploadText.textContent = 'Klik untuk ganti foto';
                            }
                        }
                    };

                    reader.readAsDataURL(this.files[0]);
                }
            });
        </script>
    @endpush
@endsection
