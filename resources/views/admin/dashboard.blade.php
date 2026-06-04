@extends('layouts.admin')

@section('title', 'Dashboard Admin - RESIK')
@section('page-title', 'Beranda')
@section('page-title-mobile', 'BERANDA')

{{-- Fallback variables jika controller tidak mengirim --}}
@php
    use Illuminate\Support\Str;
    
    $bulanList = $bulanList ?? [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $selectedBulan = $selectedBulan ?? (int) date('n');
    $selectedTahun = $selectedTahun ?? (int) date('Y');
    
    // Stats untuk RESIK
    $totalLaporan = $totalLaporan ?? 0;
    $totalTPS = $totalTPS ?? 0;
    $totalPenarikan = $totalPenarikan ?? 0;
    $totalSetor = $totalSetor ?? 0;
    $totalArtikel = $totalArtikel ?? 0;
    
    // Data chart
    $chartData = $chartData ?? [0,0,0,0,0,0,0];
    $chartLabels = $chartLabels ?? [];
    
    // Data tabel
    $laporanIllegal = $laporanIllegal ?? collect([]);
    $tahunOptions = $tahunOptions ?? [date('Y')];

    // Validasi selectedBulan
    if ($selectedBulan < 1 || $selectedBulan > 12) {
        $selectedBulan = (int) date('n');
    }
@endphp

@section('content')
<div class="dashboard-container">
    
    <!-- Header Section -->
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Beranda</h1>
            <p class="page-subtitle">Selamat datang di Dashboard Admin RESIK</p>
        </div>
        <div class="header-actions">
            <form method="GET" class="filter-form">
                <select name="tahun" class="filter-select" onchange="this.form.submit()">
                    @foreach($tahunOptions as $thn)
                        <option value="{{ $thn }}" {{ $thn == $selectedTahun ? 'selected' : '' }}>{{ $thn }}</option>
                    @endforeach
                </select>
                <select name="bulan" class="filter-select" onchange="this.form.submit()">
                    @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ $num == $selectedBulan ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="stats-grid">
        <!-- Total Laporan Sampah Ilegal -->
        <div class="stat-card card-illegal">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Laporan Sampah Ilegal</span>
                <span class="stat-value">{{ number_format($totalLaporan) }}</span>
                <span class="stat-trend text-green"></span>
            </div>
        </div>

        <!-- Total TPS -->
        <div class="stat-card card-tps">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total TPS</span>
                <span class="stat-value">{{ number_format($totalTPS) }}</span>
                <span class="stat-trend text-green"></span>
            </div>
        </div>

        <!-- Total Penarikan -->
        <div class="stat-card card-penarikan">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Penarikan</span>
                <span class="stat-value">{{ number_format($totalPenarikan) }}</span>
                <span class="stat-trend text-green"></span>
            </div>
        </div>

        <!-- Total Setor -->
        <div class="stat-card card-setor">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Setor</span>
                <span class="stat-value">{{ number_format($totalSetor) }}</span>
                <span class="stat-trend text-green"></span>
            </div>
        </div>

        <!-- Total Artikel -->
        <div class="stat-card card-artikel">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Artikel</span>
                <span class="stat-value">{{ number_format($totalArtikel) }}</span>
                <span class="stat-trend text-gray"></span>
            </div>
        </div>
    </div>

        <div class="chart-card">
        <div class="card-header">
            <h3>📊 Statistik Laporan Pengaduan Sampah Ilegal</h3>
            <span class="card-period">{{ $bulanList[$selectedBulan] }} {{ $selectedTahun }}</span>
        </div>
        <div class="chart-wrapper">
            <div class="chart-container">
                <canvas id="laporanChart"></canvas>
            </div>
        </div>
        </div>

        <!-- Tabel Laporan Sampah Illegal -->
        <div class="table-card">
            <div class="card-header">
                <h3>🗂️ Laporan Sampah Illegal</h3>
                <a href="{{ route('admin.laporan.index') }}" class="btn-view-all">Lihat Semua</a>
            </div>
            <div class="table-wrapper">
            <div class="table-responsive" id="tableContainer">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pelapor</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanIllegal as $index => $laporan)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            
                            {{-- Nama Pelapor (kolom: nama) --}}
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">{{ substr($laporan->nama ?? 'A', 0, 1) }}</div>
                                    <span>{{ $laporan->nama ?? 'Anonim' }}</span>
                                </div>
                            </td>
                            
                            {{-- Lokasi (kolom: lokasi) --}}
                            <td>{{ Str::limit($laporan->lokasi ?? '-', 25) }}</td>
                            
                            {{-- Keterangan (kolom: keterangan) - bukan jenis_sampah --}}
                            <td><span class="badge-type">{{ Str::limit($laporan->keterangan ?? 'Umum', 20) }}</span></td>
                            
                            {{-- Status (kolom: status) --}}
                            <td>
                                @php
                                    $status = $laporan->status ?? '';

                                if ($status === 'Diterima') {
                                        $statusClass = 'badge-success';
                                    } elseif ($status === 'Diproses') {
                                        $statusClass = 'badge-warning';
                                    } elseif ($status === 'Ditolak') {
                                        $statusClass = 'badge-danger';
                                    } elseif ($status === 'Ditarik') {
                                        $statusClass = 'badge-secondary';
                                    } else {
                                        $statusClass = 'badge-secondary';
                                }
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $laporan->status ?? 'Pending' }}</span>
                            </td>
                            
                            {{-- Tanggal (kolom: created_at) --}}
                            <td>{{ \Carbon\Carbon::parse($laporan->created_at ?? now())->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-icon">📭</div>
                                <p>Belum ada laporan sampah illegal</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 🔗 BRIDGE: Pass chart data ke file JS eksternal --}}
<script>
    window.DashboardConfig = {
        chartLabels: @json($chartLabels),
        chartData: @json($chartData)
    };
</script>

<!-- Chart.js CDN (WAJIB diload SEBELUM dashboard.js) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@push('scripts')
    {{-- FUNGSI: Memuat file JS eksternal untuk inisialisasi chart --}}
    <script src="{{ asset('js/dashboard.js?v=' . time()) }}"></script>
@endpush

{{-- FUNGSI: Memuat file CSS eksternal untuk dashboard admin --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css?v=' . time()) }}">
@endpush

@stack('styles')
@stack('scripts')

@endsection