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
use Illuminate\Support\Facades\Cache; // ✅ TAMBAH INI

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

        // Cari di tabel Masyarakat
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

        // Cari di tabel Pns
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
     * Simpan transaksi setor sampah
     */
    public function store(Request $request)
    {
        // ✅ DEDUPLICATION: Generate unique hash untuk request ini
        $requestHash = md5(json_encode([
            'id_masyarakat' => $request->id_masyarakat,
            'id_pns' => $request->id_pns,
            'id_jenis_sampah' => $request->id_jenis_sampah,
            'berat' => $request->berat,
            'total_rupiah' => $request->total_rupiah,
            'id_petugas' => $request->id_petugas,
            // Group by 5-second windows to allow same user to submit again after 5 sec
            'time_window' => floor(now()->timestamp / 5),
        ]));

        $cacheKey = "processed_transaction_$requestHash";

        // ✅ CEK apakah request ini sudah diproses dalam 30 detik terakhir
        if (Cache::has($cacheKey)) {
            \Log::warning('⚠️ DUPLICATE REQUEST DETECTED');
            \Log::warning('   Hash: ' . $requestHash);
            \Log::warning('   Returning cached response...');

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi sudah diproses sebelumnya',
                'data' => [
                    'duplicate' => true,
                    'request_hash' => $requestHash
                ]
            ], 200);
        }

        // Validasi input
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

            // 1. Ambil data jenis sampah untuk dapat harga
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

            // 2. Simpan transaksi ke tabel transaksi_setor
            $transaksi = TransaksiSetor::create([
                'id_masyarakat' => $request->id_masyarakat,
                'id_pns' => $request->id_pns,
                'id_jenis_sampah' => $request->id_jenis_sampah,
                'berat' => $berat,
                'harga_per_kg' => $hargaPerKg,
                'total_rupiah' => $totalRupiah,
                'id_petugas' => $request->id_petugas,
                'tanggal_transaksi' => $request->tanggal_transaksi ?? now(),
            ]);

            // ✅ 3. Update saldo & total_setoran user DENGAN LOCK + LOGGING DETAIL
            if ($request->id_masyarakat && is_numeric($request->id_masyarakat)) {
                \Log::info('=== INCREMENT MASYARAKAT START ===');
                \Log::info('User ID: ' . $request->id_masyarakat . ' | Amount: ' . $totalRupiah . ' | Berat: ' . $berat);

                // ✅ SEBELUM increment - catat saldo saat ini (dengan lock)
                $saldoSebelum = Masyarakat::lockForUpdate()
                    ->where('id_masyarakat', $request->id_masyarakat)
                    ->value('saldo');
                \Log::info('💰 Saldo SEBELUM increment: ' . $saldoSebelum);

                // ✅ Lakukan increment saldo
                Masyarakat::where('id_masyarakat', $request->id_masyarakat)
                    ->increment('saldo', $totalRupiah);

                // ✅ Lakukan increment total_setoran
                Masyarakat::where('id_masyarakat', $request->id_masyarakat)
                    ->increment('total_setoran', $berat);

                // ✅ SESUDAH increment - catat saldo baru
                $saldoSesudah = Masyarakat::where('id_masyarakat', $request->id_masyarakat)->value('saldo');
                $selisih = $saldoSesudah - $saldoSebelum;

                \Log::info('💰 Saldo SESUDAH increment: ' . $saldoSesudah);
                \Log::info('💰 Selisih aktual: ' . $selisih . ' (harusnya: ' . $totalRupiah . ')');

                if ($selisih != $totalRupiah) {
                    \Log::error('⚠️ WARNING: Selisih tidak sesuai! Mungkin ada double increment!');
                }

                \Log::info('=== INCREMENT MASYARAKAT END ===');
            } elseif ($request->id_pns && is_numeric($request->id_pns)) {
                \Log::info('=== INCREMENT PNS START ===');
                \Log::info('User ID: ' . $request->id_pns . ' | Amount: ' . $totalRupiah . ' | Berat: ' . $berat);

                // ✅ SEBELUM increment - catat saldo saat ini (dengan lock)
                $saldoSebelum = Pns::lockForUpdate()
                    ->where('id_pns', $request->id_pns)
                    ->value('saldo');
                \Log::info('💰 Saldo SEBELUM increment: ' . $saldoSebelum);

                // ✅ Lakukan increment saldo
                Pns::where('id_pns', $request->id_pns)
                    ->increment('saldo', $totalRupiah);

                // ✅ Lakukan increment total_setoran
                Pns::where('id_pns', $request->id_pns)
                    ->increment('total_setoran', $berat);

                // ✅ SESUDAH increment - catat saldo baru
                $saldoSesudah = Pns::where('id_pns', $request->id_pns)->value('saldo');
                $selisih = $saldoSesudah - $saldoSebelum;

                \Log::info('💰 Saldo SESUDAH increment: ' . $saldoSesudah);
                \Log::info('💰 Selisih aktual: ' . $selisih . ' (harusnya: ' . $totalRupiah . ')');

                if ($selisih != $totalRupiah) {
                    \Log::error('⚠️ WARNING: Selisih tidak sesuai! Mungkin ada double increment!');
                }

                \Log::info('=== INCREMENT PNS END ===');
            } else {
                \Log::warning('Tidak ada id_masyarakat atau id_pns yang valid');
            }

            DB::commit();

            // ✅ SIMPAN HASH KE CACHE SELAMA 30 DETIK (mencegah duplicate request)
            Cache::put($cacheKey, true, 30);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi setor berhasil disimpan',
                'data' => [
                    'id_transaksi' => $transaksi->id_transaksi,
                    'jenis_sampah' => $jenisSampah->jenis,
                    'berat' => $berat,
                    'harga_per_kg' => $hargaPerKg,
                    'total_rupiah' => $totalRupiah,
                    'request_hash' => $requestHash, // ✅ Tambah untuk debug
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
     * Ambil riwayat setor user
     */
    /**
     * GET /api/riwayat-setor
     * Ambil riwayat setor user
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
            // ✅ QUERY TANPA where('status') - karena kolom tidak ada di tabel
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

                    // ✅ CAST ke float agar return number, bukan string
                    'berat' => (float) ($trx->berat ?? 0),
                    'harga_per_kg' => (float) ($trx->harga_per_kg ?? 0),
                    'total_rupiah' => (float) ($trx->total_rupiah ?? 0),

                    'nama_petugas' => $trx->petugas?->nama_lengkap ?? $trx->petugas?->nama ?? '-',
                    'status' => 'selesai',
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
