@extends('layouts.admin')

@section('title', 'Edit Jenis Sampah')

@push('styles')
<style>
    .form-container {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        max-width: 800px;
        margin: 0 auto;
    }

    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e0e0e0;
    }

    .form-header h2 {
        color: #2e8b57;
        margin: 0;
        font-size: 20px;
        font-weight: 700;
    }

    .btn-back {
        background: #6c757d;
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: #5a6268;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .form-group label .required {
        color: #dc3545;
        margin-left: 3px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #2e8b57;
    }

    .form-group input.is-invalid,
    .form-group select.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .image-upload {
        border: 2px dashed #2e8b57;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 10px;
    }

    .image-upload:hover {
        background: #e8f5e9;
        border-color: #247a46;
    }

    .image-upload i {
        font-size: 40px;
        color: #2e8b57;
        margin-bottom: 12px;
        display: block;
    }

    .image-upload p {
        margin: 0 0 5px 0;
        color: #333;
        font-weight: 500;
    }

    .image-upload small {
        color: #999;
        font-size: 12px;
    }

    .image-preview {
        max-width: 180px;
        margin: 15px auto 0;
        border-radius: 8px;
        overflow: hidden;
    }

    .image-preview img {
        width: 100%;
        height: auto;
        display: block;
    }

    .form-hint {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #999;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 35px;
        padding-top: 25px;
        border-top: 2px solid #e0e0e0;
    }

    .btn {
        padding: 12px 30px;
        border-radius: 25px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-cancel {
        background: #e0e0e0;
        color: #333;
    }

    .btn-cancel:hover {
        background: #d0d0d0;
    }

    .btn-submit {
        background: #2e8b57;
        color: white;
    }

    .btn-submit:hover {
        background: #247a46;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(46, 139, 87, 0.3);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert ul {
        margin: 0;
        padding-left: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .form-actions {
            flex-direction: column;
            gap: 10px;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .form-container {
            padding: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-edit"></i> Edit Jenis Sampah</h2>
        <a href="{{ route('admin.bank-sampah.jenis-sampah.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
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

    <form action="{{ route('admin.bank-sampah.jenis-sampah.update', $jenisSampah->id_jenis_sampah) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Upload Gambar -->
        <div class="form-group full-width">
            <label>Upload Foto Jenis Sampah</label>
            <div class="image-upload" onclick="document.getElementById('gambar').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Klik untuk upload foto baru</p>
                <small>Format: JPG, PNG. Max: 2MB</small>
                <div class="image-preview" id="imagePreview">
                    @if($jenisSampah->gambar)
                        <img src="{{ asset('storage/' . $jenisSampah->gambar) }}" alt="{{ $jenisSampah->jenis }}">
                    @else
                        <img src="#" alt="Preview" style="display: none;">
                    @endif
                </div>
            </div>
            <input type="file" id="gambar" name="gambar" accept="image/*" style="display: none;" onchange="previewImage(this)">
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
                <i class="fas fa-times"></i> Batal
            </a>
            <button type="submit" class="btn btn-submit">
                <i class="fas fa-save"></i> Update
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('imagePreview');
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
