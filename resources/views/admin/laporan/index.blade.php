@extends('layouts.admin')

@section('title', 'Kelola Laporan Aduan - SIMPELSI')
@section('page-title', 'Kelola Laporan')
@section('page-title-mobile', 'LAPORAN')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
@endpush

@section('content')
{{-- Header + Search dalam satu wrapper --}}
<div class="page-search-bar"><input type="text" class="search-input" id="searchInput" placeholder="Cari data berdasarkan nama atau lokasi..."></div>
<div class="content-card">
    <h2 class="card-title">Kelola Laporan Aduan</h2>
    <div class="table-container">

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>NAMA</th>
                <th>LOKASI</th>
                <th>STATUS</th>
                <th>TANGGAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporanList as $laporan)
                @php
                    $id = $laporan->id;
                    $nama = $laporan->nama ?? '—';
                    $lokasi = $laporan->lokasi ?? '—';
                    $keterangan = $laporan->keterangan ?? '—';
                    $status = $laporan->status ?? 'Diproses';
                    $balasan = $laporan->balasan ?? '';
                    $foto = $laporan->foto ?? '';
                    $tanggal = $laporan->tanggal ? \Carbon\Carbon::parse($laporan->tanggal)->format('d-m-Y') : '—';

                    $statusClass = match($status) {
                        'Diterima' => 'diterima',
                        'Ditolak' => 'ditolak',
                        default => 'diproses'
                    };

                    $isEditable = ($status === 'Diproses');
                    $fotoUrl = $foto ? asset('storage/uploads/' . $foto) : 'https://via.placeholder.com/300x200?text=Tidak+Ada+Foto';
                @endphp

                {{-- Row Utama --}}
                <tr onclick="toggleDetail({{ $id }})" style="cursor: pointer;">
                    <td>{{ $id }}</td>
                    <td>{{ $nama }}</td>
                    <td>{{ $lokasi }}</td>
                    <td><span class="status-badge status-{{ $statusClass }}">{{ $status }}</span></td>
                    <td>{{ $tanggal }}</td>
                </tr>

                {{-- Row Detail (Expandable) --}}
                <tr class="detail-row" id="detail-{{ $id }}">
                    <td colspan="5">
                        <div class="detail-content">
                            {{-- Gambar --}}
                            <div class="detail-image">
                                <img src="{{ $fotoUrl }}" alt="Foto Laporan" onerror="this.src='https://via.placeholder.com/300x200?text=Gambar+Error'">
                            </div>

                            {{-- Form Detail --}}
                            <div class="detail-form">
                                <div class="form-group">
                                    <label class="form-label">ID Laporan:</label>
                                    <input type="text" class="form-input" value="{{ $id }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nama Pelapor:</label>
                                    <input type="text" class="form-input" value="{{ $nama }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Lokasi:</label>
                                    <input type="text" class="form-input" value="{{ $lokasi }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tanggal:</label>
                                    <input type="text" class="form-input" value="{{ $tanggal }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Keterangan:</label>
                                    <textarea class="form-textarea" readonly>{{ $keterangan }}</textarea>
                                </div>

                                @if($isEditable)
                                    {{-- Form Edit Status --}}
                                    <div class="form-group">
                                        <label class="form-label">Status:</label>
                                        <div class="status-options">
                                            <div class="status-option">
                                                <input type="radio" name="status-{{ $id }}" id="opt-diproses-{{ $id }}" value="Diproses" {{ $status === 'Diproses' ? 'checked' : '' }} onchange="onStatusChange({{ $id }})">
                                                <label for="opt-diproses-{{ $id }}">Diproses</label>
                                            </div>
                                            <div class="status-option">
                                                <input type="radio" name="status-{{ $id }}" id="opt-diterima-{{ $id }}" value="Diterima" {{ $status === 'Diterima' ? 'checked' : '' }} onchange="onStatusChange({{ $id }})">
                                                <label for="opt-diterima-{{ $id }}">Diterima</label>
                                            </div>
                                            <div class="status-option">
                                                <input type="radio" name="status-{{ $id }}" id="opt-ditolak-{{ $id }}" value="Ditolak" {{ $status === 'Ditolak' ? 'checked' : '' }} onchange="onStatusChange({{ $id }})">
                                                <label for="opt-ditolak-{{ $id }}">Ditolak</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Balasan untuk Masyarakat:</label>
                                        <textarea class="form-textarea" id="balasan-{{ $id }}" placeholder="Tulis alasan perubahan status (opsional)" {{ in_array($status, ['Diterima', 'Ditolak']) ? '' : 'disabled' }}>{{ $balasan }}</textarea>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn-secondary" onclick="closeDetail({{ $id }})">TUTUP</button>
                                        <button type="button" class="btn-primary" onclick="updateStatus({{ $id }})">SIMPAN STATUS</button>
                                    </div>
                                @else
                                    {{-- Read Only View --}}
                                    <div class="form-group">
                                        <label class="form-label">Status Akhir:</label>
                                        <input type="text" class="form-input" value="{{ $status }}" readonly>
                                    </div>
                                    @if(!empty($balasan))
                                    <div class="form-group">
                                        <label class="form-label">Balasan:</label>
                                        <textarea class="form-textarea" readonly>{{ $balasan }}</textarea>
                                    </div>
                                    @endif
                                    <div class="readonly-note">
                                        Laporan ini telah {{ strtolower($status) }}. Status tidak dapat diubah lagi.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach

            @if($laporanList->isEmpty())
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                        Tidak ada laporan untuk ditampilkan.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
</div>
{{-- Popup Modals --}}
@include('admin.laporan.partials.modals')
@endsection

@push('scripts')
    <script src="{{ asset('js/laporan.js') }}"></script>
@endpush
