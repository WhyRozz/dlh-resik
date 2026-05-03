@extends('layouts.admin')

@section('title', 'Dashboard Admin - RESIK')
@section('page-title', 'Beranda')
@section('page-title-mobile', 'BERANDA')

{{-- Fallback variables jika controller tidak mengirim --}}
@php
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
    $dateLabels = $dateLabels ?? ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
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
                <span class="stat-trend text-green">↑ 12% dari bulan lalu</span>
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
                <span class="stat-trend text-green">↑ 3 titik baru</span>
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
                <span class="stat-trend text-green">Rp 2.5Jt minggu ini</span>
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
                <span class="stat-trend text-green">↑ 18% dari bulan lalu</span>
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
                <span class="stat-trend text-gray">Edukasi lingkungan</span>
            </div>
        </div>
    </div>

    <!-- Chart & Table Section -->
    <div class="dashboard-grid">
        <!-- Chart Section -->
        <div class="chart-card">
            <div class="card-header">
                <h3>📊 Statistik Laporan Pengaduan Sampah Ilegal</h3>
                <span class="card-period">{{ $bulanList[$selectedBulan] }} {{ $selectedTahun }}</span>
            </div>
            <div class="chart-container">
                <canvas id="laporanChart"></canvas>
            </div>
        </div>

        <!-- Tabel Laporan Sampah Illegal -->
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
                                        <div class="user-avatar">{{ substr($laporan->nama ?? 'A', 0, 1) }}</div>
                                        <span>{{ $laporan->nama ?? 'Anonim' }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($laporan->lokasi ?? '-', 25) }}</td>
                                <td><span class="badge-type">{{ $laporan->jenis_sampah ?? 'Umum' }}</span></td>
                                <td>
                                    @php
                                        $statusClass = match($laporan->status ?? '') {
                                            'Diterima' => 'badge-success',
                                            'Diproses' => 'badge-warning',
                                            'Ditolak' => 'badge-danger',
                                            'Selesai' => 'badge-info',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $laporan->status ?? 'Pending' }}</span>
                                </td>
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

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom Script untuk Chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('laporanChart')?.getContext('2d');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels ?: $dateLabels),
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
                        callbacks: {
                            label: function(context) {
                                return 'Laporan: ' + context.parsed.y;
                            }
                        }
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
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
});
</script>

<!-- Custom Styles -->
@push('styles')
<style>
    :root {
        --primary: #22c55e;
        --primary-dark: #16a34a;
        --primary-light: #dcfce7;
        --bg-card: #ffffff;
        --bg-page: #f8fafc;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --radius: 16px;
    }

    .dashboard-container {
        padding: 24px;
        background: var(--bg-page);
        min-height: 100vh;
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* Header */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
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
        font-size: 14px;
    }

    .filter-form {
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
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .card-illegal .stat-icon { background: #fef3c7; color: #d97706; }
    .card-tps .stat-icon { background: #dbeafe; color: #2563eb; }
    .card-penarikan .stat-icon { background: #dcfce7; color: #22c55e; }
    .card-setor .stat-icon { background: #ede9fe; color: #7c3aed; }
    .card-artikel .stat-icon { background: #f1f5f9; color: #475569; }

    .stat-icon .icon { width: 28px; height: 28px; }

    .stat-content {
        flex: 1;
        min-width: 0;
    }

    .stat-label {
        display: block;
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 4px;
        line-height: 1.4;
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

    /* Dashboard Grid (Chart + Table) */
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
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-color);
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
        background: var(--primary-light);
        color: var(--primary-dark);
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 500;
    }

    .btn-view-all {
        font-size: 13px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .btn-view-all:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    /* Chart Container */
    .chart-container {
        height: 280px;
        position: relative;
    }

    /* Table Styles */
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
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--border-color);
        background: #f8fafc;
    }

    .data-table td {
        padding: 14px 12px;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    .data-table tr:hover {
        background: #f8fafc;
    }

    /* User Info in Table */
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--primary-light);
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
    }

    /* Badges */
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
        color: var(--text-primary);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px !important;
        color: var(--text-secondary);
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 16px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .page-title {
            font-size: 24px;
        }
        
        .stat-card {
            padding: 16px;
        }
        
        .stat-value {
            font-size: 22px;
        }
    }
</style>
@endpush
@endsection