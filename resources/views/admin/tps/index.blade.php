@extends('layouts.admin')

@section('title', 'Kelola Informasi TPS - RESIK')
@section('page-title', 'Kelola TPS')
@section('page-title-mobile', 'TPS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tps.css') }}">
@endpush

@section('content')

<div class="search-bar">
    <input type="text" class="search-input" id="searchInput" placeholder="Cari TPS berdasarkan nama atau lokasi...">
</div>

<div class="table-container">
    <!-- Header Tabel -->
    <div class="tps-header">
        <h3 class="tps-title">Daftar Informasi TPS</h3>
        <a href="{{ route('admin.tps.create') }}" class="btn-tambah-tps">
            <span>+</span> TAMBAH INFO TPS
        </a>
    </div>

    <!-- Table -->
    <table class="tps-table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama TPS</th>
                <th>Lokasi</th>
                <th style="width: 120px;">Kapasitas</th>
                <th>Keterangan</th>
                <th style="width: 100px;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tpsTableBody">
            @forelse($tpsList as $index => $tps)
                <tr data-id="{{ $tps->id_tps }}">
                    <td class="no-urut">{{ $index + 1 }}</td>
                    <td class="nama-tps">{{ $tps->nama_tps }}</td>
                    <td>
                        @if($tps->lokasi && preg_match('/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $tps->lokasi))
                            <a href="https://maps.google.com/maps?q={{ urlencode($tps->lokasi) }}"
                               target="_blank"
                               class="maps-link">
                                Lihat di maps
                            </a>
                        @else
                            <span style="color: #999;">{{ $tps->lokasi ?? '-' }}</span>
                        @endif
                    </td>
                    <td class="kapasitas">{{ $tps->kapasitas ?? '-' }}</td>
                    <td class="keterangan" title="{{ $tps->keterangan ?? '' }}">
                        {{ Str::limit($tps->keterangan ?? '-', 40) }}
                    </td>
                    <td>
                        <div class="aksi-buttons" style="display: flex; gap: 4px;">
                            {{-- ✅ Tombol Edit dengan icon gambar --}}
                            <button type="button"
                                    onclick="window.location.href='{{ route('admin.tps.edit', $tps->id_tps) }}'"
                                    style="display: inline-flex; align-items: center; justify-content: center; margin: 0 2px; padding: 6px 10px; background: #fff3cd; color: #856404; border: 1px solid #ffc107; border-radius: 4px; cursor: pointer; vertical-align: middle; transition: all 0.2s;"
                                    title="Edit"
                                    onmouseover="this.style.background='#ffeaa7'"
                                    onmouseout="this.style.background='#fff3cd'">
                                <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit" style="width: 18px; height: 18px; vertical-align: middle;">
                            </button>

                            {{-- ✅ Tombol Hapus dengan icon gambar --}}
                            <button type="button"
                                    onclick="konfirmasiHapus({{ $tps->id_tps }})"
                                    style="display: inline-flex; align-items: center; justify-content: center; margin: 0 2px; padding: 6px 10px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; cursor: pointer; vertical-align: middle; transition: all 0.2s;"
                                    title="Hapus"
                                    onmouseover="this.style.background='#f5c6cb'"
                                    onmouseout="this.style.background='#f8d7da'">
                                <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus" style="width: 18px; height: 18px; vertical-align: middle;">
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div style="font-size: 48px; margin-bottom: 10px;">🗑️</div>
                            <p>Belum ada data TPS.</p>
                            <a href="{{ route('admin.tps.create') }}">Tambah data sekarang</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Popup Modals --}}
@include('admin.tps.partials.modals')

{{-- Error Popup --}}
<div id="errorPopup" class="popup-overlay">
    <div class="popup-content error">
        <h3>Kesalahan!</h3>
        <p id="errorMessage">Terjadi kesalahan.</p>
        <button type="button" class="popup-btn" onclick="closeErrorPopup()">Tutup</button>
    </div>
</div>

{{-- 🔗 BRIDGE: Pass error message ke file JS eksternal --}}
@if($errors->any())
<script>
    window.TpsConfig = {
        errorMessage: "{{ $errors->first() }}"
    };
</script>
@endif

@push('scripts')
    {{-- FUNGSI: Memuat file JS eksternal yang berisi semua fungsi interaksi halaman --}}
    <script src="{{ asset('js/tps.js') }}"></script>
@endpush
@endsection