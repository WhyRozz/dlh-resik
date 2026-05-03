@extends('layouts.admin')

@section('title', isset($tps) ? 'Edit Informasi TPS - SIMPELSI' : 'Tambah Informasi TPS - SIMPELSI')
@section('page-title', isset($tps) ? 'Edit TPS' : 'Tambah TPS')
@section('page-title-mobile', isset($tps) ? 'EDIT' : 'TAMBAH')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tps-form.css') }}">
@endpush

@section('content')
<div class="content-header">
    <h2>{{ isset($tps) ? 'Edit' : 'Tambah' }} Informasi TPS</h2>
</div>

<div class="form-container">
    <div class="form-title">{{ isset($tps) ? 'Edit TPS' : 'Form Tambah TPS' }}</div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form id="tpsForm" method="POST" action="{{ isset($tps) ? route('admin.tps.update', $tps->id_tps) : route('admin.tps.store') }}">
        @csrf
        @if(isset($tps))
            @method('PUT')
        @endif

        {{-- Nama TPS --}}
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Nama TPS *</label>
                <input type="text"
                       name="nama_tps"
                       class="form-input"
                       value="{{ old('nama_tps', $tps->nama_tps ?? '') }}"
                       maxlength="150"
                       placeholder="Contoh: TPS Pasar Sukomoro"
                       required>
            </div>
        </div>

        {{-- Koordinat GPS --}}
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Koordinat GPS (Latitude, Longitude) *</label>
                <div class="gps-section">
                    <textarea name="lokasi"
                              class="form-textarea"
                              placeholder="Contoh: -7.601478,111.943225"
                              required>{{ old('lokasi', $tps->lokasi ?? '') }}</textarea>
                    <button type="button"
                            class="btn btn-secondary"
                            id="openMapsBtn"
                            style="height: fit-content; padding: 12px 12px;">
                        🗺️ Pilih di Maps
                    </button>
                </div>
                <small class="gps-hint">
                    1. Klik tombol "Pilih di Maps"<br>
                    2. Klik lokasi di Google Maps → koordinat muncul di kiri bawah<br>
                    3. Salin & tempel ke kolom di atas<br>
                    Format: <code>-7.601478,111.943225</code>
                </small>
            </div>
        </div>

        {{-- Alamat Lengkap --}}
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Alamat Lengkap *</label>
                <textarea name="alamat"
                          class="form-textarea"
                          placeholder="Contoh: Jl. Merdeka No. 15, Kel. Beran, Kec. Nganjuk"
                          required>{{ old('alamat', $tps->alamat ?? '') }}</textarea>
            </div>
        </div>

        {{-- Kapasitas (Opsional) --}}
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kapasitas (opsional)</label>
                <input type="text"
                       name="kapasitas"
                       class="form-input"
                       value="{{ old('kapasitas', $tps->kapasitas ?? '') }}"
                       maxlength="20"
                       placeholder="Contoh: 100">
            </div>
        </div>

        {{-- Keterangan (Opsional) --}}
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Keterangan (opsional)</label>
                <textarea name="keterangan"
                          class="form-textarea"
                          placeholder="Informasi tambahan tentang TPS ini">{{ old('keterangan', $tps->keterangan ?? '') }}</textarea>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="action-buttons">
            <a href="{{ route('admin.tps.index') }}" class="btn btn-secondary">❌ BATAL</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($tps) ? '💾 SIMPAN PERUBAHAN' : '📤 SIMPAN TPS' }}
            </button>
        </div>
    </form>
</div>

{{-- Error Popup --}}
<div id="errorPopup" class="popup-overlay">
    <div class="popup-content error">
        <h3>Kesalahan!</h3>
        <p id="errorMessage">Terjadi kesalahan.</p>
        <button class="popup-btn" onclick="closeErrorPopup()">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Pass koordinat to JS --}}
    <script>
        window.tpsFormConfig = {
            koordinat: @json($tps->lokasi ?? null)
        };
    </script>
    <script src="{{ asset('js/tps-form.js') }}"></script>
@endpush
