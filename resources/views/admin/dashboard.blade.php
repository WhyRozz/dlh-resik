@extends('layouts.admin')

@section('title', 'Dashboard Admin - SIMPELSI') //tes3
@section('page-title', 'Beranda')
<<<<<<< HEAD
@section('page-title-mobile', 'BERANDA')
=======
@section('page-title-mobile', 'BERANDA') // tes ajaa baru 10
>>>>>>> ad3e449 (save perubahan)

{{-- Fallback variables jika controller tidak mengirim --}}
@php
    $bulanList = $bulanList ?? [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $selectedBulan = $selectedBulan ?? (int) date('n');
    $selectedTahun = $selectedTahun ?? (int) date('Y');
    $total = $total ?? 0;
    $selesai_diproses = $selesai_diproses ?? 0;
    $belum_diproses = $belum_diproses ?? 0;
    $ditolak = $ditolak ?? 0;
    $dateLabels = $dateLabels ?? ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
    $counts = $counts ?? [0,0,0,0,0,0,0];
    $recentReports = $recentReports ?? collect([]);
    $tahunOptions = $tahunOptions ?? [date('Y')];

    // Validasi selectedBulan
    if ($selectedBulan < 1 || $selectedBulan > 12) {
        $selectedBulan = (int) date('n');
    }
@endphp

@section('content')
<div class="content-header"> 
    <h2>Statistik Laporan</h2>
</div>

<div class="stats-container">
    <div class="stats-header">
        <h3>Statistik Laporan – {{ $bulanList[$selectedBulan] ?? 'Bulan' }} {{ $selectedTahun }}</h3>

        <div class="filter-controls">
            <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div>
                    <label for="tahun">Tahun:</label>
                    <select name="tahun" id="tahun">
                        @foreach($tahunOptions as $thn)
                            <option value="{{ $thn }}" {{ $thn == $selectedTahun ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="bulan">Bulan:</label>
                    <select name="bulan" id="bulan">
                        @foreach($bulanList as $num => $nama)
                            <option value="{{ $num }}" {{ $num == $selectedBulan ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit">Tampilkan</button>
                <a href="{{ route('admin.dashboard') }}">Reset</a>
            </form>
        </div>
    </div>

    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-number">{{ $total }}</div>
            <div class="stat-label">Total Laporan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $selesai_diproses }}</div>
            <div class="stat-label">Selesai Diproses</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $belum_diproses }}</div>
            <div class="stat-label">Belum Diproses</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $ditolak }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>

    <div class="trend-chart">
        <h4>Trend Laporan (7 Hari Terakhir)</h4>
        <div class="mini-bar-chart">
            @php
                $maxCount = !empty($counts) ? max($counts) : 1;
                if ($maxCount == 0) $maxCount = 1;
            @endphp
            @foreach($dateLabels as $i => $dayAbbr)
                @php
                    $fullDate = date('Y-m-d', strtotime("-" . (6 - $i) . " days"));
                    $count = $counts[$i] ?? 0;
                    $height = ($count / $maxCount) * 80;
                    if ($height < 10) $height = 10;
                @endphp
                <div class="bar-container">
                    <div class="bar-value">{{ $count }}</div>
                    <div class="bar" style="height: {{ $height }}px;"></div>
                    <div class="bar-label">{{ date('d M', strtotime($fullDate)) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="recent-reports">
        <h4>Laporan Terbaru</h4>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Alamat</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentReports as $r)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}</td>
                        <td>{{ $r->alamat }}</td>
                        <td>
                            @php
                                $statusClass = match($r->status ?? '') {
                                    'Diterima' => 'status-green',
                                    'Diproses' => 'status-yellow',
                                    'Ditolak' => 'status-red',
                                    default => 'status-yellow'
                                };
                            @endphp
                            <span class="status {{ $statusClass }}">{{ $r->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 20px;">Tidak ada laporan terbaru</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
