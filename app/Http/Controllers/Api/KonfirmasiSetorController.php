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
use App\Services\NotificationService;

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
                ->where('status_transaksi', '!=', 'dibatalkan')
                ->orderBy('tanggal_transaksi', 'desc')
                ->get()
                ->map(function ($trx) {
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
                        'can_edit' => $canEdit && !$isExpired,
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
     * ✅ SALDO DITAMBAHKAN DI SINI SAAT KONFIRMASI
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

        if ($transaksi->status_transaksi === 'dibatalkan') {
            return response()->json(['status' => 'error', 'message' => 'Transaksi sudah dibatalkan, tidak bisa dikonfirmasi'], 400);
        }

        if ($transaksi->tanggal_koreksi) {
            return response()->json(['status' => 'error', 'message' => 'Transaksi sudah dikonfirmasi sebelumnya'], 400);
        }

        if ($transaksi->berat_asli !== null) {
            return response()->json(['status' => 'error', 'message' => 'Data sudah pernah diedit, tidak bisa diedit lagi'], 400);
        }

        try {
            DB::transaction(function () use ($request, $transaksi) {
                $jenis = JenisSampah::find($request->id_jenis_sampah);

                // Simpan berat asli jika ada perubahan
                if ($transaksi->berat != $request->berat || $transaksi->id_jenis_sampah != $request->id_jenis_sampah) {
                    $transaksi->berat_asli = $transaksi->berat;
                    $transaksi->status_transaksi = 'dikoreksi'; // ✅ ENUM: dikoreksi
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

                // ✅✅✅ INCREMENT SALDO HANYA SAAT KONFIRMASI ✅✅✅
                if ($transaksi->id_masyarakat) {
                    Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                        ->increment('saldo', $transaksi->total_rupiah);      // ✅ Saldo
                    Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                        ->increment('total_setoran', $transaksi->berat);     // ✅ Total setoran
                } elseif ($transaksi->id_pns) {
                    Pns::where('id_pns', $transaksi->id_pns)
                        ->increment('saldo', $transaksi->total_rupiah);
                    Pns::where('id_pns', $transaksi->id_pns)
                        ->increment('total_setoran', $transaksi->berat);
                }
            });

            $transaksi->refresh();

            $user = $transaksi->masyarakat ?? $transaksi->pns;

            $notification = new NotificationService();

            $notification->sendDepositResult(
                $user?->fcm_token,
                $transaksi->total_rupiah
            );

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
     * ✅ SALDO JUGA DITAMBAHKAN DI SINI UNTUK AUTO-CONFIRM
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
                    // Update status (pakai ENUM yang valid)
                    $transaksi->status_transaksi = 'selesai'; // ✅ ENUM: selesai
                    $transaksi->catatan_koreksi = 'Auto-confirmed setelah 24 jam';
                    $transaksi->tanggal_koreksi = now();
                    $transaksi->save();

                    // ✅✅✅ INCREMENT SALDO UNTUK AUTO-CONFIRM ✅✅✅
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

                $user = $transaksi->masyarakat ?? $transaksi->pns;

                $notification = new NotificationService();

                $notification->sendDepositResult(
                    $user?->fcm_token,
                    $transaksi->total_rupiah
                );

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

        if ($transaksi->tanggal_koreksi || $transaksi->status_transaksi === 'dibatalkan') {
            return response()->json(['status' => 'error', 'message' => 'Transaksi sudah diproses'], 400);
        }


        $transaksi->status_transaksi = 'dibatalkan'; // ✅ ENUM: dibatalkan
        $transaksi->save();

        $user = $transaksi->masyarakat ?? $transaksi->pns;

        $notification = new NotificationService();

        $notification->sendDepositRejected(
            $user?->fcm_token
        );

        return response()->json(['status' => 'success', 'message' => 'Transaksi ditolak'], 200);
    }

    /**
     * GET /api/setor-statistics/{id_petugas}
     */
    public function getStatistics($idPetugas)
    {
        try {
            $today = Carbon::today();

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

            $hariIni = TransaksiSetor::where('id_petugas', $idPetugas)
                ->whereDate('tanggal_transaksi', $today)
                ->count();

            $totalNominalHariIni = TransaksiSetor::where('id_petugas', $idPetugas)
                ->whereDate('tanggal_transaksi', $today)
                ->whereNotNull('tanggal_koreksi')
                ->sum('total_rupiah');

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
     */
    public function getHistory($idPetugas, Request $request)
    {
        try {
            $status = $request->query('status', 'all');

            $query = TransaksiSetor::with(['jenisSampah', 'masyarakat', 'pns'])
                ->where('id_petugas', $idPetugas)
                ->orderBy('tanggal_transaksi', 'desc');

            if ($status === 'pending') {
                $query->whereNull('tanggal_koreksi')
                    ->where('status_transaksi', '!=', 'dibatalkan');
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
                    'can_edit' => $trx->berat_asli === null
                        && !$isExpired
                        && $trx->tanggal_koreksi === null
                        && $trx->status_transaksi !== 'dibatalkan',
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
