<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiSetor;
use Illuminate\Http\Request;

class RiwayatSetorAdminController extends Controller
{
    /**
     * GET /api/riwayat-setor-admin/{id_petugas}
     */
    public function index($idPetugas)
    {
        try {
            $riwayat = TransaksiSetor::with(['jenisSampah', 'masyarakat', 'pns'])
                ->where('id_petugas', $idPetugas)
                ->orderBy('tanggal_transaksi', 'desc')
                ->get()
                ->map(function($trx) {
                    return [
                        'id_transaksi' => $trx->id_transaksi,
                        'tanggal_transaksi' => $trx->tanggal_transaksi,
                        'jenis_sampah' => $trx->jenisSampah?->jenis ?? 'Umum',
                        'berat' => (float) ($trx->berat ?? 0),
                        'harga_per_kg' => (float) ($trx->harga_per_kg ?? 0),
                        'total_rupiah' => (float) ($trx->total_rupiah ?? 0),
                        'nama_pengguna' => $trx->masyarakat?->nama ?? $trx->pns?->nama ?? '-',
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $riwayat,
                'total' => $riwayat->count()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Riwayat Setor Admin Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat riwayat setor: ' . $e->getMessage()
            ], 500);
        }
    }
}
