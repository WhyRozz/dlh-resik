<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;              // ✅ TAMBAHKAN IMPORT INI
use App\Models\TransaksiSetor;
use App\Models\Penarikan;
use App\Models\Penjemputan;
use App\Models\JenisSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubAdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // ✅ TAMBAHKAN PHPDoc TYPE HINT INI (KUNCI UTAMA!)
        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        // Validasi: harus Sub Admin Desa
        if (!$admin || !$admin->isSubAdminDesa() || !$admin->id_desa) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $idDesa = $admin->id_desa;

        // ========== STATS CARDS ==========
        $totalSetor = TransaksiSetor::whereHas('masyarakat.desa', function ($q) use ($idDesa) {
            $q->where('id_desa', $idDesa);
        })->count();

        $totalPenarikan = Penarikan::whereHas('masyarakat.desa', function ($q) use ($idDesa) {
            $q->where('id_desa', $idDesa);
        })->orWhereHas('pns.desa', function ($q) use ($idDesa) {
            $q->where('id_desa', $idDesa);
        })->count();

        $totalJenisSampah = JenisSampah::count(); // Global, tidak difilter

        $totalPenjemputan = DB::table('penjemputans')
            ->where('nama_admin', 'LIKE', '%bank_sampah_' . $idDesa . '%')
            ->count();

        // ========== CHART DATA ==========
        $selectedBulan = (int) ($request->input('bulan') ?? date('n'));
        $selectedTahun = (int) ($request->input('tahun') ?? date('Y'));

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedBulan, $selectedTahun);
        $chartLabels = [];
        $chartData = [];

        for ($week = 1; $week <= 5; $week++) {
            $startDay = ($week - 1) * 7 + 1;

            if ($startDay > $daysInMonth) {
                break;
            }

            $endDay = min($startDay + 6, $daysInMonth);

            $startDate = sprintf('%04d-%02d-%02d 00:00:00', $selectedTahun, $selectedBulan, $startDay);
            $endDate = sprintf('%04d-%02d-%02d 23:59:59', $selectedTahun, $selectedBulan, $endDay);

            $count = TransaksiSetor::whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->whereHas('masyarakat.desa', function ($q) use ($idDesa) {
                    $q->where('id_desa', $idDesa);
                })
                ->count();

            $chartLabels[] = 'Minggu ' . $week;
            $chartData[] = $count;
        }

        // ========== DATA TERBARU ==========
        $penarikanTerbaru = Penarikan::with(['masyarakat.desa', 'pns.desa'])
            ->whereHas('masyarakat.desa', function ($q) use ($idDesa) {
                $q->where('id_desa', $idDesa);
            })
            ->orWhereHas('pns.desa', function ($q) use ($idDesa) {
                $q->where('id_desa', $idDesa);
            })
            ->orderBy('tanggal_penarikan', 'desc')
            ->limit(5)
            ->get();

        $penjemputanTerbaru = DB::table('penjemputans')
            ->where('nama_admin', 'LIKE', '%bank_sampah_' . $idDesa . '%')
            ->orderBy('waktu', 'desc')
            ->limit(5)
            ->get();

        // ========== OPTIONS FILTER ==========
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

        $tahunOptions = range(2025, 2030);
        rsort($tahunOptions);

        // BUAT VARIABEL ADMIN USER SEBELUM COMPACT
        $adminUser = Auth::guard('admin')->user();

        return view('admin.sub-admin.dashboard', compact(
            'totalSetor',
            'totalPenarikan',
            'totalJenisSampah',
            'totalPenjemputan',
            'chartLabels',
            'chartData',
            'penarikanTerbaru',
            'penjemputanTerbaru',
            'bulanList',
            'selectedBulan',
            'selectedTahun',
            'tahunOptions',
            'adminUser'
        ));
    }
}
