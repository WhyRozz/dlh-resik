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
        // ========== FILTER BULAN & TAHUN (Hanya untuk dropdown) ==========
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $selectedTahun = (int) ($request->input('tahun') ?? date('Y'));
        $selectedBulan = (int) ($request->input('bulan') ?? date('n'));

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

// Hapus minggu kosong di akhir (jika bulan tidak penuh 4 minggu)
$daysInMonth = Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->daysInMonth;
$weeksInMonth = ceil($daysInMonth / 7);
$chartLabels = array_slice($chartLabels, 0, $weeksInMonth);
$chartData = array_slice($chartData, 0, $weeksInMonth);

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
        return view('admin.dashboard', compact(
            'bulanList',
            'selectedTahun',
            'selectedBulan',
            'tahunOptions',
            'totalLaporan',
            'totalTPS',
            'totalPenarikan',
            'totalSetor',
            'totalArtikel',
            'chartLabels',
            'chartData',
            'laporan'
        ));
    }
}