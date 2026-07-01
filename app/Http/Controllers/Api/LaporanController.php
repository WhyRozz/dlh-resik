<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class LaporanController extends Controller
{
    /**
     * Submit Laporan Baru (POST /api/laporan)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'tipe'    => 'required|in:masyarakat,pns',
            'nama'    => 'required|string|max:100',
            'tanggal' => 'required|date',
            'lokasi'  => 'required|string|max:255',
            'keterangan' => 'required|string',
            'foto'    => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userId = $request->user_id;
            $tipe   = $request->tipe;

            // ✅ Validasi user berdasarkan tipe
            if ($tipe === 'masyarakat') {

                $user = \App\Models\Masyarakat::with('desa')->find($userId);
            } else {

                $user = \App\Models\Pns::with('dinas')->find($userId);
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // ✅ Upload Foto
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('laporan', 'public');
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Foto wajib diupload'
                ], 422);
            }

            // ✅ Simpan ke Database dengan kolom yang benar
            $laporan = Laporan::create([
                'id_masyarakat' => $tipe === 'masyarakat' ? $userId : null,
                'id_pns'        => $tipe === 'pns' ? $userId : null,
                'nama'          => $request->nama,
                'tanggal'       => $request->tanggal,
                'lokasi'        => $request->lokasi,
                'keterangan'    => $request->keterangan,
                'foto'          => $path,
                'status'        => 'Diproses',
                'balasan'       => null,
            ]);

            $notification = new NotificationService();

            if ($tipe == 'masyarakat') {

                $notification->sendReport(
                    $user->nama,
                    optional($user->desa)->nama_desa ?? "Tidak diketahui",
                    $user->id_desa,
                    $laporan->id
                );
            } else {

                $notification->sendReport(
                    $user->nama,
                    optional($user->dinas)->nama_dinas ?? "DLH",
                    0,
                    $laporan->id
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil dikirim',
                'data' => ['id' => $laporan->id]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Laporan Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Riwayat Laporan User (GET /api/laporan)
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $tipe = $request->query('tipe');

        if (!$userId || !$tipe) {
            return response()->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 422);
        }

        try {
            $query = \App\Models\Laporan::query();

            // ✅ Filter berdasarkan tipe user
            if ($tipe === 'masyarakat') {
                $query->where('id_masyarakat', $userId);
            } elseif ($tipe === 'pns') {
                $query->where('id_pns', $userId);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Tipe user tidak valid'], 422);
            }

            $laporans = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($l) {
                    // ✅ CEK: Apakah URL sudah lengkap (http://)?
                    $fotoUrl = $l->foto;

                    if ($fotoUrl) {
                        // Jika sudah http://, return apa adanya
                        if (str_starts_with($fotoUrl, 'http')) {
                            $fotoUrl = $fotoUrl;
                        } else {
                            // Jika masih path relatif, tambahkan asset()
                            $fotoUrl = asset('storage/' . $fotoUrl);
                        }
                    }

                    return [
                        'id' => $l->id,
                        'judul' => strlen($l->keterangan) > 30 ? substr($l->keterangan, 0, 30) . '...' : $l->keterangan,
                        'keterangan' => $l->keterangan,
                        'alamat' => $l->lokasi,
                        'lokasi' => $l->lokasi,
                        'tanggal' => $l->tanggal ? $l->tanggal->format('l, d F Y') : '-',
                        'status' => $l->status,
                        'foto' => $fotoUrl, // ✅ URL yang sudah di-fix
                        'nama' => $l->nama,
                        'balasan' => $l->balasan,
                    ];
                });

            return response()->json(['status' => 'success', 'data' => $laporans]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
