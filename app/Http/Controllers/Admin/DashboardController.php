<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Tps;
use App\Models\Penarikan;
use App\Models\TransaksiSetor;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
<<<<<<< HEAD
        // ========== 1. FILTER BULAN & TAHUN ==========
=======
        // ========== FILTER BULAN & TAHUN (Hanya untuk dropdown) ==========
>>>>>>> 71e0b2fc491735604765e0f62f2e1aa5303cbb0f
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $selectedTahun = (int) ($request->input('tahun') ?? date('Y'));
        $selectedBulan = (int) ($request->input('bulan') ?? date('n'));

<<<<<<< HEAD
        // Validasi input
        if ($selectedBulan < 1 || $selectedBulan > 12) $selectedBulan = (int) date('n');
        if ($selectedTahun < 2000 || $selectedTahun > date('Y') + 1) $selectedTahun = (int) date('Y');

        // ========== 2. QUERY LAPORAN BULANAN (UNTUK STATS LAMA) ==========
        $total = Laporan::where(function ($query) use ($selectedTahun, $selectedBulan) {
            $query->whereNotNull('tanggal')
                ->whereYear('tanggal', $selectedTahun)
                ->whereMonth('tanggal', $selectedBulan);
        })
        ->orWhere(function ($query) use ($selectedTahun, $selectedBulan) {
            $query->whereNull('tanggal')
                ->whereYear('created_at', $selectedTahun)
                ->whereMonth('created_at', $selectedBulan);
        })->count();

        $statusCounts = Laporan::selectRaw('status, COUNT(*) as total')
            ->where(function ($query) use ($selectedTahun, $selectedBulan) {
                $query->whereNotNull('tanggal')
                    ->whereYear('tanggal', $selectedTahun)
                    ->whereMonth('tanggal', $selectedBulan);
            })
            ->orWhere(function ($query) use ($selectedTahun, $selectedBulan) {
                $query->whereNull('tanggal')
                    ->whereYear('created_at', $selectedTahun)
                    ->whereMonth('created_at', $selectedBulan);
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
=======
        // ========== 1. STATISTIK (TOTAL KESELURUHAN - TANPA FILTER!) ==========
        $totalLaporan = Laporan::count(); // ✅ TOTAL SEMUA
        $totalTPS = Tps::count(); // ✅ TOTAL SEMUA
        $totalPenarikan = Penarikan::count(); // ✅ TOTAL SEMUA
        $totalSetor = TransaksiSetor::count(); // ✅ TOTAL SEMUA
        $totalArtikel = Artikel::count(); // ✅ TOTAL SEMUA

       // Group by minggu dalam bulan
$chartLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
$chartData = [];

for ($week = 1; $week <= 4; $week++) {
    $startOfWeek = Carbon::createFromDate($selectedTahun, $selectedBulan, 1)
        ->addWeeks($week - 1)
        ->startOfWeek();
    
    $endOfWeek = $startOfWeek->copy()->endOfWeek();
    
    // Jangan melebihi akhir bulan
    $endOfMonth = Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->endOfMonth();
    if ($endOfWeek > $endOfMonth) {
        $endOfWeek = $endOfMonth;
    }
    
    $count = Laporan::whereBetween('created_at', [
        $startOfWeek->format('Y-m-d 00:00:00'),
        $endOfWeek->format('Y-m-d 23:59:59')
    ])->count();
    
    $chartData[] = $count;
}
>>>>>>> 71e0b2fc491735604765e0f62f2e1aa5303cbb0f

// Hapus minggu kosong di akhir (jika bulan tidak penuh 4 minggu)
$daysInMonth = Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->daysInMonth;
$weeksInMonth = ceil($daysInMonth / 7);
$chartLabels = array_slice($chartLabels, 0, $weeksInMonth);
$chartData = array_slice($chartData, 0, $weeksInMonth);

<<<<<<< HEAD
        // ========== 3. STATS CARDS (TOTAL DATA REAL) ==========
        $totalLaporan = Laporan::count();
        $totalTPS = Tps::count();
        $totalPenarikan = Penarikan::count();
        $totalSetor = TransaksiSetor::count();
        $totalArtikel = Artikel::count();

        // ========== 4. ✅ CHART DATA (PER MINGGU 1-5) ==========
        // Sumbu X: Minggu 1, 2, 3, 4, [5]
        // Sumbu Y: Jumlah laporan (0, 10, 20, ...)
        
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedBulan, $selectedTahun);
        $chartLabels = [];
        $chartData = [];

        // Loop maksimal 5 minggu
        for ($week = 1; $week <= 5; $week++) {
            $startDay = ($week - 1) * 7 + 1;  // 1, 8, 15, 22, 29
            
            // Jika tanggal mulai sudah melebihi jumlah hari di bulan, berhenti
            if ($startDay > $daysInMonth) {
                break;
            }

            $endDay = min($startDay + 6, $daysInMonth);  // 7, 14, 21, 28, [30/31]

            // Format tanggal untuk query database
            $startDate = sprintf('%04d-%02d-%02d 00:00:00', $selectedTahun, $selectedBulan, $startDay);
            $endDate = sprintf('%04d-%02d-%02d 23:59:59', $selectedTahun, $selectedBulan, $endDay);

            // Hitung total laporan di minggu tersebut
            $count = Laporan::whereBetween('created_at', [$startDate, $endDate])->count();

            // Simpan ke array
            $chartLabels[] = 'Minggu ' . $week;
            $chartData[] = $count;
        }

        // Untuk kompatibilitas view lama
        $counts = $chartData;

        // ========== 5. TABEL 5 LAPORAN TERBARU ==========
        $laporanIllegal = Laporan::select('id', 'nama', 'lokasi', 'keterangan', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ========== 6. LAPORAN LAMA (4 ITEM - KOMPATIBILITAS) ==========
        $recentReports = Laporan::select('lokasi as alamat', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // ========== 7. OPTIONS FILTER TAHUN (MANUAL 2025 - 2040) ==========
        $tahunOptions = range(2025, 2030);
        rsort($tahunOptions);

        // ========== 8. KIRIM SEMUA VARIABLE KE VIEW ==========
=======
        // ========== 3. LIST LAPORAN TERBARU (10 DATA) ==========
        $laporan = Laporan::latest()
            ->take(10)
            ->get();

        // ========== 4. TAHUN OPTIONS (UNTUK DROPDOWN) ==========
        $tahunOptions = Laporan::selectRaw('DISTINCT YEAR(created_at) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->filter()
            ->toArray();

        if (empty($tahunOptions)) {
            $tahunOptions = [date('Y')];
        }

        // ========== 5. RETURN VIEW ==========
>>>>>>> 71e0b2fc491735604765e0f62f2e1aa5303cbb0f
        return view('admin.dashboard', compact(
            // Filter
            'bulanList',
            'selectedTahun',
            'selectedBulan',
            'tahunOptions',
<<<<<<< HEAD
            
            // Stats Lama
            'total',
            'selesai_diproses',
            'belum_diproses',
            'ditolak',
            
            // Stats Cards Baru
=======
>>>>>>> 71e0b2fc491735604765e0f62f2e1aa5303cbb0f
            'totalLaporan',
            'totalTPS',
            'totalPenarikan',
            'totalSetor',
            'totalArtikel',
<<<<<<< HEAD
            
            // Chart Data
            'chartLabels',
            'chartData',
            'counts',
            
            // Tabel Data
            'recentReports',
            'laporanIllegal'
=======
            'chartLabels',
            'chartData',
            'laporan'
>>>>>>> 71e0b2fc491735604765e0f62f2e1aa5303cbb0f
        ));
    }
}