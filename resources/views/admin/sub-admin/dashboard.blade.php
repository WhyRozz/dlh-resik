@extends('layouts.admin')

@section('title', 'Dashboard Bank Sampah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush

@section('content')
<div class="dashboard-container">
    
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="page-title">Dashboard Bank Sampah</h1>
            <p class="page-subtitle">
                {{ Auth::guard('admin')->user()->desa->nama_desa ?? '-' }}, 
                {{ Auth::guard('admin')->user()->kecamatan->nama_kecamatan ?? '-' }}
            </p>
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

    <!-- Stats Cards -->
    <div class="stats-grid">
        <!-- Total Setor -->
        <div class="stat-card card-setor">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Setor</span>
                <span class="stat-value">{{ number_format($totalSetor) }}</span>
            </div>
        </div>

        <!-- Total Penarikan -->
        <div class="stat-card card-penarikan">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Penarikan</span>
                <span class="stat-value">{{ number_format($totalPenarikan) }}</span>
            </div>
        </div>

        <!-- Total Jenis Sampah -->
        <div class="stat-card card-artikel">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Jenis Sampah</span>
                <span class="stat-value">{{ number_format($totalJenisSampah) }}</span>
            </div>
        </div>

        <!-- Total Penjemputan -->
        <div class="stat-card card-tps">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Penjemputan</span>
                <span class="stat-value">{{ number_format($totalPenjemputan) }}</span>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-card">
        <div class="card-header">
            <h3>📊 Grafik Setor Sampah</h3>
            <span class="card-period">{{ $bulanList[$selectedBulan] }} {{ $selectedTahun }}</span>
        </div>
        <div class="chart-wrapper">
            <div class="chart-container">
                <canvas id="setorChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Terbaru -->
    <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
        <!-- Penarikan Terbaru -->
        <div class="table-card">
            <div class="card-header">
                <h3>💰 Penarikan Terbaru</h3>
                <a href="{{ route('admin.bank-sampah.penarikan.index') }}" class="btn-view-all">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penarikanTerbaru as $penarikan)
                            <tr>
                                <td>{{ $penarikan->tanggal_penarikan->format('d M Y') }}</td>
                                <td>Rp {{ number_format($penarikan->jumlah_uang, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $penarikan->status === 'berhasil' ? 'badge-success' : ($penarikan->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ ucfirst($penarikan->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Penjemputan Terbaru -->
        <div class="table-card">
            <div class="card-header">
                <h3>🚛 Penjemputan Terbaru</h3>
                <a href="{{ route('admin.bank-sampah.penjemputan.index') }}" class="btn-view-all">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Berat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjemputanTerbaru as $penjemputan)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($penjemputan->waktu)->format('d M Y H:i') }}</td>
                                <td>{{ number_format($penjemputan->berat, 2) }} Kg</td>
                                <td>
                                    <span class="badge {{ $penjemputan->status === 'disetujui' ? 'badge-success' : ($penjemputan->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ ucfirst($penjemputan->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('setorChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Jumlah Setor',
                data: @json($chartData),
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#22c55e',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endsection