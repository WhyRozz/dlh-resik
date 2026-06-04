@extends('layouts.admin')

@section('title', 'Daftar TPS - RESIK')
@section('page-title', 'Kelola TPS')
@section('page-title-mobile', 'INFORMASI TPS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tps.css?v=' . time()) }}">
@endpush

@section('content')
<div class="page-container">
    
    {{-- 1. SEARCH BAR (Baris atas sendiri, pojok kanan) --}}
    <div class="search-top">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari nama TPS..." value="{{ request('search') }}">
        </div>
    </div>

    {{-- 2. JUDUL & TOMBOL (Sejajar di baris kedua) --}}
    <div class="content-header">
        <h2 class="page-title">Data Informasi TPS</h2>
        <button type="button" onclick="location.href='{{ route('admin.tps.create') }}'" class="btn-add">
            <i class="fas fa-plus"></i> Tambah Info TPS
        </button>
    </div>
    
    {{-- 3. GARIS HIJAU --}}
    <div class="green-divider"></div>
    
    {{-- 4. TABEL --}}
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 10%;">No</th>
                    <th style="width: 20%;">Nama TPS</th>
                    <th style="width: 20%;">Lokasi</th>
                    <th style="width: 20%;">Kapasitas</th>
                    <th style="width: 20%;">Keterangan</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tpsList as $index => $tps)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $tps->nama_tps }}</strong></td>
                    <td>
                        @if($tps->lokasi && preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', trim($tps->lokasi)))
                            @php
                                [$lat, $lng] = array_map('trim', explode(',', $tps->lokasi));
                            @endphp
                            <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" 
                               target="_blank" 
                               class="maps-link">
                                <i class="fas fa-map-marker-alt"></i> Lihat di Maps
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($tps->kapasitas)
                            <span class="kapasitas-badge">{{ $tps->kapasitas }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="keterangan-text">{{ Str::limit($tps->keterangan ?? '-', 50) }}</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.tps.edit', $tps->id_tps) }}" 
                               class="btn-icon btn-edit" title="Edit">
                                <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit" style="width: 18px; height: 18px; object-fit: contain;">
                            </a>
                            <button type="button" 
                                    class="btn-icon btn-delete" 
                                    title="Hapus"
                                    onclick="konfirmasiHapus({{ $tps->id_tps }})">
                                <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus" style="width: 18px; height: 18px; object-fit: contain;">
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada data TPS</p>
                            <a href="{{ route('admin.tps.create') }}" class="btn-add" style="margin-top: 10px;">
                                <i class="fas fa-plus"></i> Tambah data TPS sekarang
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- CSRF Bridge --}}
<script>
    window.TpsConfig = { csrfToken: "{{ csrf_token() }}" };
</script>

@push('scripts')
    {{-- ✅ WAJIB: Load SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Load JS eksternal --}}
    <script src="{{ asset('js/tps.js?v=' . time()) }}"></script>
    
    {{-- ✅ BENAR: Cek session flash message --}}
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            position: 'center'
        });
    </script>
    @endif
    
    {{-- ✅ SweetAlert untuk Validation Errors --}}
    @if($errors->any())
    <script>
        let errorHtml = '<ul style="text-align: left; margin: 10px 0; padding-left: 20px;">';
        @foreach($errors->all() as $error)
            errorHtml += '<li>{{ $error }}</li>';
        @endforeach
        errorHtml += '</ul>';
        
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan!',
            html: errorHtml,
            confirmButtonColor: '#dc2626',
            position: 'center'
        });
    </script>
    @endif
@endpush
@endsection