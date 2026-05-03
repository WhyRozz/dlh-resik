<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Definisi bulan
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

        // Ambil input dengan default
        $selectedTahun = (int) ($request->input('tahun') ?? date('Y'));
        $selectedBulan = (int) ($request->input('bulan') ?? date('n'));

        // Validasi
        if ($selectedBulan < 1 || $selectedBulan > 12) {
            $selectedBulan = (int) date('n');
        }
        if ($selectedTahun < 2000 || $selectedTahun > date('Y') + 1) {
            $selectedTahun = (int) date('Y');
        }

        // Query total laporan
        $total = Laporan::where(function ($query) use ($selectedTahun, $selectedBulan) {
            $query->whereNotNull('tanggal')
                ->whereYear('tanggal', $selectedTahun)
                ->whereMonth('tanggal', $selectedBulan);
        })
            ->orWhere(function ($query) use ($selectedTahun, $selectedBulan) {
                $query->whereNull('tanggal')
                    ->whereYear('created_at', $selectedTahun)
                    ->whereMonth('created_at', $selectedBulan);
            })
            ->count();

        // Query status counts
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

        // Trend 7 hari terakhir
        $dateLabels = [];
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateLabels[] = date('D', strtotime("-$i days"));
            $counts[] = 0; // Default 0
        }

        // Laporan terbaru (4 item)
        $recentReports = Laporan::select('lokasi as alamat', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // ✅ TAHUN OPTIONS - Ambil tahun unik dari database
        $tahunOptions = Laporan::selectRaw('DISTINCT YEAR(COALESCE(tanggal, created_at)) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->filter()
            ->toArray();

        // Jika tidak ada data, gunakan tahun sekarang
        if (empty($tahunOptions)) {
            $tahunOptions = [date('Y')];
        }

        // ✅ KIRIM SEMUA VARIABLE KE VIEW
        return view('admin.dashboard', compact(
            'bulanList',
            'selectedTahun',
            'selectedBulan',
            'total',
            'selesai_diproses',
            'belum_diproses',
            'ditolak',
            'dateLabels',
            'counts',
            'recentReports',
            'tahunOptions'  // ✅ PASTIKAN INI ADA
        ));
    }
}
