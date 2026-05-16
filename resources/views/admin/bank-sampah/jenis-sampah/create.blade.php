@extends('layouts.admin')

@section('title', 'Tambah Jenis Sampah - Admin RESIK')

@push('styles')
    {{-- FUNGSI: Memuat file CSS khusus untuk halaman jenis sampah --}}
    <link rel="stylesheet" href="{{ asset('css/jenis-sampah.css') }}">
@endpush

@section('content')
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-plus"></i> Tambah Jenis Sampah</h2>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>Terjadi kesalahan:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.bank-sampah.jenis-sampah.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Upload Gambar -->
        <div class="form-group full-width">
            <label>Upload Foto Jenis Sampah</label>
            <div class="image-upload">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Klik untuk upload foto</p>
                <small>Format: JPG, PNG. Max: 2MB</small>
                <div class="image-preview" id="imagePreview"></div>
            </div>
            <input type="file" id="gambar" name="gambar" accept="image/*" style="display: none;" onchange="previewImage(this)">
            @error('gambar')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <!-- Jenis Sampah -->
        <div class="form-group full-width">
            <label>Jenis Sampah <span class="required">*</span></label>
            <input type="text" name="jenis" value="{{ old('jenis') }}" placeholder="Contoh: Plastik, Kertas, Logam" required>
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
                <input type="number" name="harga" value="{{ old('harga') }}" placeholder="Contoh: 2000" min="0" required>
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
    {{-- FUNGSI: Memuat file JS eksternal yang berisi semua fungsi form handling --}}
    <script src="{{ asset('js/jenis-sampah.js') }}"></script>
@endpush
@endsection