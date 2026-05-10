<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiSetor;
use App\Models\Masyarakat;
use App\Models\Pns;
use App\Models\JenisSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KonfirmasiSetorController extends Controller
{
    /**
     * GET /api/setor-need-confirmation/{id_petugas}
     */
    public function getNeedConfirmation($idPetugas)
    {
        try {
            $list = TransaksiSetor::with(['jenisSampah', 'masyarakat', 'pns'])
                ->where('id_petugas', $idPetugas)
                ->whereNull('tanggal_koreksi')
                ->orderBy('tanggal_transaksi', 'desc')
                ->get()
                ->map(function ($trx) {
                    // ✅ Cek apakah sudah pernah diedit (berat_asli tidak null)
                    $canEdit = $trx->berat_asli === null;
                    $isExpired = Carbon::parse($trx->tanggal_transaksi)->diffInHours(Carbon::now()) >= 24;

                    return [
                        'id_transaksi' => $trx->id_transaksi,
                        'tanggal_transaksi' => $trx->tanggal_transaksi,
                        'jenis_sampah' => $trx->jenisSampah?->jenis ?? 'Umum',
                        'id_jenis_sampah' => $trx->id_jenis_sampah,
                        'berat' => (float) ($trx->berat ?? 0),
                        'harga_per_kg' => (float) ($trx->harga_per_kg ?? 0),
                        'total_rupiah' => (float) ($trx->total_rupiah ?? 0),
                        'nama_pengguna' => $trx->masyarakat?->nama ?? $trx->pns?->nama ?? '-',
                        'id_masyarakat' => $trx->id_masyarakat,
                        'id_pns' => $trx->id_pns,
                        'status_transaksi' => $trx->status_transaksi,
                        'berat_asli' => $trx->berat_asli,
                        'can_edit' => $canEdit && !$isExpired, // ✅ Hanya bisa edit jika belum pernah & < 24 jam
                        'is_expired' => $isExpired,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $list,
                'total' => $list->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/jenis-sampah-list
     * Ambil semua jenis sampah untuk dropdown
     */
    public function getJenisSampah()
    {
        try {
            $jenis = JenisSampah::orderBy('jenis', 'asc')->get()->map(function ($item) {
                return [
                    'id_jenis_sampah' => $item->id_jenis_sampah,
                    'jenis' => $item->jenis,
                    'harga' => (float) ($item->harga ?? 0),
                    'satuan' => $item->satuan ?? 'kg',
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $jenis
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/konfirmasi-setor/{id_transaksi}
     */
    public function confirm(Request $request, $idTransaksi)
    {
        $request->validate([
            'id_jenis_sampah' => 'required|integer|exists:jenis_sampah,id_jenis_sampah',
            'berat' => 'required|numeric|min:0.1',
            'id_petugas' => 'required|integer',
            'catatan' => 'nullable|string',
        ]);

        $transaksi = TransaksiSetor::findOrFail($idTransaksi);

        // ✅ Cek apakah sudah pernah dikonfirmasi
        if ($transaksi->tanggal_koreksi) {
            return response()->json(['status' => 'error', 'message' => 'Transaksi sudah dikonfirmasi sebelumnya'], 400);
        }

        // ✅ Cek apakah sudah pernah diedit (hanya boleh 1x)
        if ($transaksi->berat_asli !== null) {
            return response()->json(['status' => 'error', 'message' => 'Data sudah pernah diedit, tidak bisa diedit lagi'], 400);
        }

        try {
            DB::transaction(function () use ($request, $transaksi) {
                $jenis = JenisSampah::find($request->id_jenis_sampah);

                // Simpan berat asli jika ada perubahan
                if ($transaksi->berat != $request->berat || $transaksi->id_jenis_sampah != $request->id_jenis_sampah) {
                    $transaksi->berat_asli = $transaksi->berat;
                    $transaksi->status_transaksi = 'dikoreksi';
                    $transaksi->catatan_koreksi = $request->catatan ?? 'Dikoreksi saat konfirmasi';
                }

                // Update data final
                $transaksi->id_jenis_sampah = $request->id_jenis_sampah;
                $transaksi->berat = $request->berat;
                $transaksi->harga_per_kg = $jenis->harga;
                $transaksi->total_rupiah = $request->berat * $jenis->harga;
                $transaksi->dikoreksi_oleh = $request->id_petugas;
                $transaksi->tanggal_koreksi = now();
                $transaksi->save();

                // ✅ INCREMENT SALDO
                if ($transaksi->id_masyarakat) {
                    Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                        ->increment('saldo', $transaksi->total_rupiah);
                    Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                        ->increment('total_setoran', $transaksi->berat);
                } elseif ($transaksi->id_pns) {
                    Pns::where('id_pns', $transaksi->id_pns)
                        ->increment('saldo', $transaksi->total_rupiah);
                    Pns::where('id_pns', $transaksi->id_pns)
                        ->increment('total_setoran', $transaksi->berat);
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil dikonfirmasi & saldo user ditambahkan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/auto-confirm-setor
     * Auto-confirm transaksi yang sudah > 24 jam
     */
    public function autoConfirm()
    {
        try {
            $expiredTransaksi = TransaksiSetor::whereNull('tanggal_koreksi')
                ->where('tanggal_transaksi', '<', Carbon::now()->subHours(24))
                ->get();

            $count = 0;
            foreach ($expiredTransaksi as $transaksi) {
                DB::transaction(function () use ($transaksi) {
                    // Update tanpa edit (pakai data yang ada)
                    $transaksi->status_transaksi = 'confirmed_auto';
                    $transaksi->catatan_koreksi = 'Auto-confirmed setelah 24 jam';
                    $transaksi->tanggal_koreksi = now();
                    $transaksi->save();

                    // Increment saldo
                    if ($transaksi->id_masyarakat) {
                        Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                            ->increment('saldo', $transaksi->total_rupiah);
                        Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                            ->increment('total_setoran', $transaksi->berat);
                    } elseif ($transaksi->id_pns) {
                        Pns::where('id_pns', $transaksi->id_pns)
                            ->increment('saldo', $transaksi->total_rupiah);
                        Pns::where('id_pns', $transaksi->id_pns)
                            ->increment('total_setoran', $transaksi->berat);
                    }
                });
                $count++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil auto-confirm $count transaksi"
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/tolak-setor/{id_transaksi}
     */
    public function reject($idTransaksi)
    {
        $transaksi = TransaksiSetor::findOrFail($idTransaksi);

        if ($transaksi->tanggal_koreksi) {
            return response()->json(['status' => 'error', 'message' => 'Sudah dikonfirmasi, tidak bisa ditolak'], 400);
        }

        $transaksi->status_transaksi = 'dibatalkan';
        $transaksi->save();

        return response()->json(['status' => 'success', 'message' => 'Transaksi ditolak'], 200);
    }
    /**
     * GET /api/setor-statistics/{id_petugas}
     * Statistik & summary transaksi
     */
    public function getStatistics($idPetugas)
    {
        try {
            $today = Carbon::today();

            // Total per status
            $pending = TransaksiSetor::where('id_petugas', $idPetugas)
                ->whereNull('tanggal_koreksi')
                ->count();

            $selesai = TransaksiSetor::where('id_petugas', $idPetugas)
                ->whereNotNull('tanggal_koreksi')
                ->where('status_transaksi', '!=', 'dibatalkan')
                ->count();

            $dibatalkan = TransaksiSetor::where('id_petugas', $idPetugas)
                ->where('status_transaksi', 'dibatalkan')
                ->count();

            // Transaksi hari ini
            $hariIni = TransaksiSetor::where('id_petugas', $idPetugas)
                ->whereDate('tanggal_transaksi', $today)
                ->count();

            // Total nominal hari ini (yang sudah confirmed)
            $totalNominalHariIni = TransaksiSetor::where('id_petugas', $idPetugas)
                ->whereDate('tanggal_transaksi', $today)
                ->whereNotNull('tanggal_koreksi')
                ->sum('total_rupiah');

            // Total berat hari ini
            $totalBeratHariIni = TransaksiSetor::where('id_petugas', $idPetugas)
                ->whereDate('tanggal_transaksi', $today)
                ->whereNotNull('tanggal_koreksi')
                ->sum('berat');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'pending' => $pending,
                    'selesai' => $selesai,
                    'dibatalkan' => $dibatalkan,
                    'hari_ini' => $hariIni,
                    'total_nominal_hari_ini' => (float) $totalNominalHariIni,
                    'total_berat_hari_ini' => (float) $totalBeratHariIni,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/setor-history/{id_petugas}
     * History dengan filter status
     */
    public function getHistory($idPetugas, Request $request)
    {
        try {
            $status = $request->query('status', 'all'); // all, pending, selesai, dibatalkan

            $query = TransaksiSetor::with(['jenisSampah', 'masyarakat', 'pns'])
                ->where('id_petugas', $idPetugas)
                ->orderBy('tanggal_transaksi', 'desc');

            if ($status === 'pending') {
                $query->whereNull('tanggal_koreksi');
            } elseif ($status === 'selesai') {
                $query->whereNotNull('tanggal_koreksi')
                    ->where('status_transaksi', '!=', 'dibatalkan');
            } elseif ($status === 'dibatalkan') {
                $query->where('status_transaksi', 'dibatalkan');
            }

            $list = $query->get()->map(function ($trx) {
                $isExpired = Carbon::parse($trx->tanggal_transaksi)->diffInHours(Carbon::now()) >= 24;

                return [
                    'id_transaksi' => $trx->id_transaksi,
                    'tanggal_transaksi' => $trx->tanggal_transaksi,
                    'jenis_sampah' => $trx->jenisSampah?->jenis ?? 'Umum',
                    'id_jenis_sampah' => $trx->id_jenis_sampah,
                    'berat' => (float) ($trx->berat ?? 0),
                    'berat_asli' => $trx->berat_asli ? (float) $trx->berat_asli : null,
                    'harga_per_kg' => (float) ($trx->harga_per_kg ?? 0),
                    'total_rupiah' => (float) ($trx->total_rupiah ?? 0),
                    'nama_pengguna' => $trx->masyarakat?->nama ?? $trx->pns?->nama ?? '-',
                    'id_masyarakat' => $trx->id_masyarakat,
                    'id_pns' => $trx->id_pns,
                    'status_transaksi' => $trx->status_transaksi,
                    'tanggal_koreksi' => $trx->tanggal_koreksi,
                    'catatan_koreksi' => $trx->catatan_koreksi,
                    'can_edit' => $trx->berat_asli === null && !$isExpired && $trx->tanggal_koreksi === null,
                    'is_expired' => $isExpired,
                    'is_confirmed' => $trx->tanggal_koreksi !== null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $list,
                'total' => $list->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
