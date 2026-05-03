@extends('layouts.admin')

@section('title', 'Kelola Informasi TPS - SIMPELSI')
@section('page-title', 'Kelola TPS')
@section('page-title-mobile', 'TPS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/tps.css') }}">
@endpush

@section('content')
<div class="content-header">
    <h2>Kelola TPS</h2>
</div>

<div class="search-bar">
    <input type="text" class="search-input" id="searchInput" placeholder="Cari TPS berdasarkan nama atau lokasi...">
</div>

<div class="table-container">
    <!-- ✅ Header Tabel dengan styling baru -->
    <div class="tps-header">
        <h3 class="tps-title">Daftar Informasi TPS</h3>
        <a href="{{ route('admin.tps.create') }}" class="btn-tambah-tps">
            <span>+</span> TAMBAH INFO TPS
        </a>
    </div>

    <!-- ✅ Table dengan class tps-table -->
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
                <tr>
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
                        <div class="aksi-buttons">
                            <a href="{{ route('admin.tps.edit', $tps->id_tps) }}"
                               class="btn-aksi btn-edit"
                               title="Edit">✏️</a>
                            <button type="button"
                                    class="btn-aksi btn-delete"
                                    title="Hapus"
                                    onclick="konfirmasiHapus({{ $tps->id_tps }})">🗑️</button>
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
@endsection

@push('scripts')
    <script src="{{ asset('js/tps.js') }}"></script>
@endpush
