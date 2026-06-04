@extends('layouts.admin')

@section('title', isset($tps) ? 'Edit Informasi TPS - RESIK' : 'Tambah Informasi TPS - RESIK')
@section('page-title', isset($tps) ? 'Edit TPS' : 'Tambah TPS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tps-form.css?v=' . time()) }}">
@endpush

@section('content')

<div class="form-wrapper">
    <div class="form-header">
        <div class="header-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="header-text">
            <h1>{{ isset($tps) ? 'Edit Informasi TPS' : 'Form Tambah TPS' }}</h1>
            <p>{{ isset($tps) ? 'Perbarui data TPS yang sudah ada' : 'Isi form di bawah untuk menambah data TPS baru' }}</p>
        </div>
    </div>

    <div class="form-container">
        {{-- Error Alert --}}
        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form id="tpsForm" method="POST" action="{{ isset($tps) ? route('admin.tps.update', $tps->id_tps) : route('admin.tps.store') }}">
            @csrf
            @if(isset($tps))
                @method('PUT')
            @endif

                <div class="form-row">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-building"></i>
                            Nama TPS <span class="required">*</span>
                        </label>
                        <input type="text"
                               name="nama_tps"
                               class="form-input"
                               value="{{ old('nama_tps', $tps->nama_tps ?? '') }}"
                               maxlength="150"
                               placeholder="Contoh: TPS Pasar Sukomoro"
                               required>
                        <small class="input-hint">Masukkan nama TPS yang jelas dan mudah dikenali</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marked-alt"></i>
                            Koordinat GPS <span class="required">*</span>
                        </label>
                        <div class="gps-input-group">
                            <textarea name="lokasi"
                                      class="form-textarea gps-textarea"
                                      placeholder="-7.601478,111.943225"
                                      required>{{ old('lokasi', $tps->lokasi ?? '') }}</textarea>
                            <button type="button"
                                    class="btn btn-maps"
                                    id="openMapsBtn">
                                <i class="fas fa-map"></i>
                                <span>Pilih di Maps</span>
                            </button>
                        </div>
                        <small class="input-hint">
                            Format: <code>latitude,longitude</code> (Contoh: -7.601478,111.943225)
                        </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-home"></i>
                            Alamat Lengkap <span class="required">*</span>
                        </label>
                        <textarea name="alamat"
                                  class="form-textarea"
                                  rows="3"
                                  placeholder="Contoh: Jl. Merdeka No. 15, Kel. Beran, Kec. Nganjuk"
                                  required>{{ old('alamat', $tps->alamat ?? '') }}</textarea>
                </div>
            </div>

                <div class="form-row two-columns">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-database"></i>
                            Kapasitas Kg
                        </label>
                        <input type="text"
                               name="kapasitas"
                               class="form-input"
                               value="{{ old('kapasitas', $tps->kapasitas ?? '') }}"
                               maxlength="20"
                               placeholder="Contoh: 100">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-info-circle"></i>
                            Keterangan
                        </label>
                        <input type="text"
                               name="keterangan"
                               class="form-input"
                               value="{{ old('keterangan', $tps->keterangan ?? '') }}"
                               maxlength="255"
                               placeholder="Informasi tambahan">
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="form-actions">
                <a href="{{ route('admin.tps.index') }}" class="btn btn-secondary">
                    <span>Batal</span>
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span>{{ isset($tps) ? 'Simpan Perubahan' : 'Tambah TPS Baru' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Error Popup --}}
<div id="errorPopup" class="popup-overlay">
    <div class="popup-content error">
        <div class="popup-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Kesalahan!</h3>
        <p id="errorMessage">Terjadi kesalahan.</p>
        <button class="popup-btn" onclick="closeErrorPopup()">
            <i class="fas fa-check"></i> Mengerti
        </button>
    </div>
</div>

@endsection

@push('scripts')
    <script>
        window.tpsFormConfig = {
            koordinat: @json($tps->lokasi ?? null)
        };
    </script>
    <script src="{{ asset('js/tps-form.js?v=' . time()) }}"></script>
@endpush