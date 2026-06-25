<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Tps;
use App\Models\Penarikan;
use App\Models\TransaksiSetor;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {


        // ========== 1. FILTER BULAN & TAHUN ==========
        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $selectedTahun = (int) ($request->input('tahun') ?? date('Y'));
        $selectedBulan = (int) ($request->input('bulan') ?? date('n'));

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

        $diproses = $statusCounts['Diproses'] ?? 0;
        $diterima = $statusCounts['Diterima'] ?? 0;
        $ditolak = $statusCounts['Ditolak'] ?? 0;
        $selesai_diproses = $diterima;
        $belum_diproses = $diproses;

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
        // ✅ BUAT VARIABEL DULU SEBELUM COMPACT
        $adminUser = auth()->guard('admin')->user();

        return view('admin.dashboard', compact(
            // Filter
            'bulanList',
            'selectedTahun',
            'selectedBulan',
            'tahunOptions',

            // Stats Lama
            'total',
            'selesai_diproses',
            'belum_diproses',
            'ditolak',

            // Stats Cards Baru
            'totalLaporan',
            'totalTPS',
            'totalPenarikan',
            'totalSetor',
            'totalArtikel',

            // Chart Data
            'chartLabels',
            'chartData',
            'counts',

            // Tabel Data
            'recentReports',
            'laporanIllegal',

            // ADMIN USER (tanpa =>, cukup nama variabelnya saja)
            'adminUser'
        ));
    }
}
