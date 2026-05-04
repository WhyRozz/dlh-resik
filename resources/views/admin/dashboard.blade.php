@extends('layouts.admin')

@section('title', 'Dashboard Admin - RESIK')
@section('page-title', 'Beranda')
@section('page-title-mobile', 'BERANDA')

{{-- Fallback variables --}}
@php
    $bulanList = $bulanList ?? [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $selectedBulan = $selectedBulan ?? (int) date('n');
    $selectedTahun = $selectedTahun ?? (int) date('Y');
    
    $totalLaporan = $totalLaporan ?? 0;
    $totalTPS = $totalTPS ?? 0;
    $totalPenarikan = $totalPenarikan ?? 0;
    $totalSetor = $totalSetor ?? 0;
    $totalArtikel = $totalArtikel ?? 0;
    
    // Chart data - fallback
    $chartLabels = $chartLabels ?? ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
    $chartData = $chartData ?? [0,0,0,0,0,0,0];
    
    // Table data - gunakan $laporan jika $laporanIllegal tidak ada
    $laporanIllegal = $laporanIllegal ?? $laporan ?? collect([]);
    $tahunOptions = $tahunOptions ?? [date('Y')];
@endphp

@section('content')
<div class="dashboard-container">
    
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Beranda</h1>
            <p class="page-subtitle">Selamat datang di Dashboard Admin RESIK</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <!-- Total Laporan -->
        <div class="stat-card card-illegal">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
                <span class="stat-label">Total Laporan Sampah Ilegal</span>
                <span class="stat-value">{{ number_format($totalLaporan) }}</span>
                <span class="stat-trend text-green">↑ Total Laporan sampah yang masuk</span>
            </div>
        </div>

        <!-- Total TPS -->
        <div class="stat-card card-tps">
            <div class="stat-icon">🏢</div>
            <div class="stat-content">
                <span class="stat-label">Total TPS</span>
                <span class="stat-value">{{ number_format($totalTPS) }}</span>
                <span class="stat-trend text-green">Total tps di nganjuk</span>
            </div>
        </div>

        <!-- Total Penarikan -->
        <div class="stat-card card-penarikan">
            <div class="stat-icon">💵</div>
            <div class="stat-content">
                <span class="stat-label">Total Penarikan</span>
                <span class="stat-value">{{ number_format($totalPenarikan) }}</span>
                <span class="stat-trend text-green">↑ Total penarikan minggu ini</span>
            </div>
        </div>

        <!-- Total Setor -->
        <div class="stat-card card-setor">
            <div class="stat-icon">📤</div>
            <div class="stat-content">
                <span class="stat-label">Total Setor</span>
                <span class="stat-value">{{ number_format($totalSetor) }}</span>
                <span class="stat-trend text-green">↑ Total setor keseluruhan</span>
            </div>
        </div>

        <!-- Total Artikel -->
        <div class="stat-card card-artikel">
            <div class="stat-icon">📰</div>
            <div class="stat-content">
                <span class="stat-label">Total Artikel</span>
                <span class="stat-value">{{ number_format($totalArtikel) }}</span>
                <span class="stat-trend text-gray">Edukasi lingkungan</span>
            </div>
        </div>
    </div>

    <!-- Chart & Table Section -->
    <div class="dashboard-grid">
        
        <!-- Chart Card -->
        <div class="chart-card">
            <div class="card-header">
                <div>
                    <h3>📊 Statistik Laporan Pengaduan Sampah Ilegal</h3>
                    <p class="card-period">Grafik Harian - {{ $bulanList[$selectedBulan] ?? 'Semua' }} {{ $selectedTahun }}</p>
                </div>
                
                <!-- Filter Form - Inline dengan judul -->
                <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-form-inline">
                    <select name="bulan" class="filter-select" onchange="this.form.submit()">
                        @foreach($bulanList as $num => $nama)
                            <option value="{{ $num }}" {{ $num == $selectedBulan ? 'selected' : '' }}>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                    
                    <select name="tahun" class="filter-select" onchange="this.form.submit()">
                        @foreach($tahunOptions as $thn)
                            <option value="{{ $thn }}" {{ $thn == $selectedTahun ? 'selected' : '' }}>
                                {{ $thn }}
                            </option>
                        @endforeach
                    </select>
                    
                    <button type="button" onclick="resetFilter()" class="btn-reset">Reset</button>
                </form>
            </div>
            
            <div class="chart-container">
                <canvas id="laporanChart"></canvas>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="card-header">
                <h3>🗂️ Laporan Sampah Illegal</h3>
                <a href="{{ route('admin.laporan.index') }}" class="btn-view-all">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pelapor</th>
                            <th>Lokasi</th>
                            <th>Jenis Sampah</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanIllegal->take(5) as $index => $laporan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">{{ substr($laporan->nama ?? $laporan->user->nama ?? 'A', 0, 1) }}</div>
                                        <span>{{ $laporan->nama ?? $laporan->user->nama ?? 'Anonim' }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($laporan->lokasi ?? '-', 25) }}</td>
                                <td><span class="badge-type">{{ $laporan->jenis_sampah ?? 'Umum' }}</span></td>
                                <td>
                                    @php
                                        $status = strtolower($laporan->status ?? '');
                                        $statusClass = match(true) {
                                            str_contains($status, 'selesai') => 'badge-success',
                                            str_contains($status, 'proses') => 'badge-warning',
                                            str_contains($status, 'tolak') => 'badge-danger',
                                            str_contains($status, 'terima') => 'badge-info',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($laporan->status ?? 'Pending') }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($laporan->created_at ?? now())->format('d M Y H:i') }}</td>
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

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function resetFilter() {
    window.location.href = "{{ route('admin.dashboard') }}";
}

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('laporanChart')?.getContext('2d');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: @json($chartData),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>

<style>
:root {
    --primary: #22c55e;
    --bg-card: #ffffff;
    --bg-page: #f8fafc;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
    --radius: 16px;
}

.dashboard-container {
    padding: 24px;
    background: var(--bg-page);
    min-height: 100vh;
}

.dashboard-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.page-subtitle {
    color: var(--text-secondary);
    margin: 4px 0 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

/* 🔥 HOVER EFFECT UNTUK STAT CARD */
.stat-card {
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    border-color: var(--primary);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1);
}

.card-illegal .stat-icon { background: #fef3c7; }
.card-tps .stat-icon { background: #dbeafe; }
.card-penarikan .stat-icon { background: #dcfce7; }
.card-setor .stat-icon { background: #ede9fe; }
.card-artikel .stat-icon { background: #f1f5f9; }

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 4px;
}

.stat-value {
    display: block;
    font-size: 26px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.stat-trend {
    font-size: 12px;
    color: var(--text-secondary);
}

.text-green { color: #22c55e !important; }
.text-gray { color: var(--text-secondary) !important; }

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

.chart-card,
.table-card {
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border: 1px solid var(--border-color);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
    flex-wrap: wrap;
    gap: 16px;
}

.card-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.card-period {
    font-size: 13px;
    color: var(--text-secondary);
    margin: 4px 0 0;
}

.filter-form-inline {
    display: flex;
    gap: 12px;
    align-items: center;
}

.filter-select {
    padding: 10px 16px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    background: var(--bg-card);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-select:hover {
    border-color: var(--primary);
    background: #f0fdf4;
}

.btn-reset {
    padding: 10px 16px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    background: var(--bg-card);
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
}

.btn-reset:hover {
    background: #f1f5f9;
    border-color: var(--primary);
}

.btn-view-all {
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    transition: opacity 0.2s;
}

.btn-view-all:hover {
    opacity: 0.8;
}

.chart-container {
    height: 280px;
    position: relative;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.data-table th {
    text-align: left;
    padding: 14px 12px;
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 12px;
    text-transform: uppercase;
    border-bottom: 2px solid var(--border-color);
    background: #f8fafc;
}

.data-table td {
    padding: 14px 12px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #dcfce7;
    color: #166534;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}

.badge-success { background: #dcfce7; color: #166534; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-info { background: #dbeafe; color: #1e40af; }
.badge-secondary { background: #f1f5f9; color: #475569; }

.badge-type {
    padding: 4px 10px;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 12px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px !important;
    color: var(--text-secondary);
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
}
</style>
@endsection