<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use App\Models\Pns;
use App\Models\JenisSampah;
use App\Models\TransaksiSetor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class SetorController extends Controller
{
    /**
     * POST /api/cari-pengguna
     * Cari user berdasarkan barcode_id
     */
    public function cariPengguna(Request $request)
    {
        $barcodeId = trim($request->kode_qr);

        if (!$barcodeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode barcode kosong'
            ], 400);
        }

        \Log::info('=== SCAN QR CODE ===');
        \Log::info('Barcode yang diterima: ' . $barcodeId);

        $masyarakat = Masyarakat::select('id_masyarakat', 'nama', 'email', 'no_telepon', 'barcode_id', 'saldo')
            ->where('barcode_id', $barcodeId)
            ->first();

        if ($masyarakat) {
            \Log::info('✅ User ditemukan di masyarakat: ' . $masyarakat->nama);
            return response()->json([
                'status' => 'success',
                'data' => $masyarakat,
                'tipe' => 'masyarakat'
            ]);
        }

        $pns = Pns::select('id_pns', 'nama', 'email', 'no_telepon', 'barcode_id', 'saldo', 'kode_anggota')
            ->where('barcode_id', $barcodeId)
            ->first();

        if ($pns) {
            \Log::info('✅ User ditemukan di pns: ' . $pns->nama);
            return response()->json([
                'status' => 'success',
                'data' => $pns,
                'tipe' => 'pns'
            ]);
        }

        \Log::warning('❌ User TIDAK DITEMUKAN dengan barcode: ' . $barcodeId);
        return response()->json([
            'status' => 'error',
            'message' => 'Pengguna tidak ditemukan'
        ], 404);
    }

    /**
     * POST /api/transaksi-setor
     * Simpan transaksi setor - SALDO TIDAK LANGSUNG MASUK (menunggu konfirmasi)
     */
    public function store(Request $request)
    {
        $requestHash = md5(json_encode([
            'id_masyarakat' => $request->id_masyarakat,
            'id_pns' => $request->id_pns,
            'id_jenis_sampah' => $request->id_jenis_sampah,
            'berat' => $request->berat,
            'total_rupiah' => $request->total_rupiah,
            'id_petugas' => $request->id_petugas,
            'time_window' => floor(now()->timestamp / 5),
        ]));

        $cacheKey = "processed_transaction_$requestHash";

        if (Cache::has($cacheKey)) {
            \Log::warning('⚠️ DUPLICATE REQUEST DETECTED');
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi sudah diproses sebelumnya',
                'data' => ['duplicate' => true, 'request_hash' => $requestHash]
            ], 200);
        }

        $validator = Validator::make($request->all(), [
            'id_masyarakat' => 'nullable|integer|exists:masyarakat,id_masyarakat',
            'id_pns' => 'nullable|integer|exists:pns,id_pns',
            'id_petugas' => 'required|integer|exists:petugas,id_petugas',
            'id_jenis_sampah' => 'required|integer|exists:jenis_sampah,id_jenis_sampah',
            'berat' => 'required|numeric|min:0.1',
            'tanggal_transaksi' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->id_masyarakat) {
                $user = Masyarakat::with('desa')
                    ->find($request->id_masyarakat);
            } else {
                $user = Pns::with(['desa', 'dinas'])
                    ->find($request->id_pns);
            }

            $jenisSampah = JenisSampah::find($request->id_jenis_sampah);
            if (!$jenisSampah) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jenis sampah tidak ditemukan'
                ], 404);
            }

            $hargaPerKg = $jenisSampah->harga;
            $berat = $request->berat;
            $totalRupiah = $berat * $hargaPerKg;

            // ✅ Simpan transaksi dengan status 'selesai' (default ENUM)
            // ❌ SALDO TIDAK LANGSUNG INCREMENT DI SINI
            $transaksi = TransaksiSetor::create([
                'id_masyarakat' => $request->id_masyarakat,
                'id_pns' => $request->id_pns,
                'id_jenis_sampah' => $request->id_jenis_sampah,
                'berat' => $berat,
                'harga_per_kg' => $hargaPerKg,
                'total_rupiah' => $totalRupiah,
                'id_petugas' => $request->id_petugas,
                'tanggal_transaksi' => $request->tanggal_transaksi ?? now(),
                'status_transaksi' => 'selesai', // ✅ Default ENUM
            ]);
            // ❌❌❌ INCREMENT SALDO DIHAPUS DARI SINI ❌❌❌
            // Saldo hanya akan ditambah saat konfirmasi (confirm/autoConfirm)

            DB::commit();
            Cache::put($cacheKey, true, 30);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi setor berhasil disimpan (menunggu konfirmasi)',
                'data' => [
                    'id_transaksi' => $transaksi->id_transaksi,
                    'jenis_sampah' => $jenisSampah->jenis,
                    'berat' => $berat,
                    'harga_per_kg' => $hargaPerKg,
                    'total_rupiah' => $totalRupiah,
                    'status_transaksi' => 'selesai',
                    'request_hash' => $requestHash,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transaksi Setor Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/riwayat-setor
     */
    public function riwayatSetor(Request $request)
    {
        $idMasyarakat = $request->query('id_masyarakat');
        $idPns = $request->query('id_pns');
        $tipeUser = $request->query('tipe_user');

        if (!$tipeUser || (!$idMasyarakat && !$idPns)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter user tidak lengkap'
            ], 422);
        }

        try {
            $query = TransaksiSetor::with(['jenisSampah', 'petugas'])
                ->orderBy('tanggal_transaksi', 'desc');

            if ($tipeUser === 'masyarakat' && $idMasyarakat) {
                $query->where('id_masyarakat', $idMasyarakat);
            } elseif ($tipeUser === 'pns' && $idPns) {
                $query->where('id_pns', $idPns);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipe user atau ID tidak valid'
                ], 422);
            }

            $riwayat = $query->get()->map(function ($trx) {
                return [
                    'id_transaksi' => $trx->id_transaksi,
                    'tanggal_transaksi' => $trx->tanggal_transaksi,
                    'jenis_sampah' => $trx->jenisSampah?->jenis ?? 'Umum',
                    'berat' => (float) ($trx->berat ?? 0),
                    'harga_per_kg' => (float) ($trx->harga_per_kg ?? 0),
                    'total_rupiah' => (float) ($trx->total_rupiah ?? 0),
                    'nama_petugas' => $trx->petugas?->nama_lengkap ?? $trx->petugas?->nama ?? '-',
                    'status' => $trx->status_transaksi ?? 'selesai',
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $riwayat,
                'total' => $riwayat->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Riwayat Setor Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
