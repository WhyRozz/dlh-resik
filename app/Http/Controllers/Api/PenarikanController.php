<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Masyarakat;
use App\Models\Pns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenarikanController extends Controller
{
    /**
     * Submit Penarikan Baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_masyarakat' => 'nullable|integer|exists:masyarakat,id_masyarakat',
            'id_pns' => 'nullable|integer|exists:pns,id_pns',
            'tipe_user' => 'required|in:masyarakat,pns',
            'nama' => 'required|string|max:255',
            'jenis_ewallet' => 'required|in:Dana,OVO,GoPay,ShopeePay',
            'nomor_ewallet' => 'required|string|min:10|max:20',
            'jumlah_uang' => 'required|numeric|min:50000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Tentukan user ID berdasarkan tipe
            $userId = $request->tipe_user == 'masyarakat'
                ? $request->id_masyarakat
                : $request->id_pns;

            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID user tidak valid'
                ], 400);
            }

            // Cek user & saldo
            $user = $request->tipe_user == 'masyarakat'
                ? Masyarakat::find($userId)
                : Pns::find($userId);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Cek saldo cukup
            if ($user->saldo < $request->jumlah_uang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Saldo tidak mencukupi. Saldo Anda: Rp ' . number_format($user->saldo, 0, ',', '.')
                ], 400);
            }

            // Potong saldo user
            $user->saldo -= $request->jumlah_uang;
            $user->save();

            // Simpan penarikan
            $penarikan = Penarikan::create([
                'id_masyarakat' => $request->tipe_user == 'masyarakat' ? $userId : null,
                'id_pns' => $request->tipe_user == 'pns' ? $userId : null,

                // Kolom baru (Biarkan null dulu karena user belum approve/disetujui)
                'updated_by' => null,
                'tanggal_disetujui' => null,

                'jumlah_uang' => $request->jumlah_uang,
                'jenis_ewallet' => $request->jenis_ewallet,
                'nomor_ewallet' => $request->nomor_ewallet,
                'status' => 'diproses',
                'tanggal_penarikan' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan penarikan berhasil diajukan',
                'data' => [
                    'id_penarikan' => $penarikan->id_penarikan,
                    'jumlah_uang' => $penarikan->jumlah_uang,
                    'status' => $penarikan->status,
                    'tanggal' => $penarikan->tanggal_penarikan->format('d-m-Y H:i'),
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Riwayat Penarikan User
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_masyarakat' => 'nullable|integer',
            'id_pns' => 'nullable|integer',
            'tipe_user' => 'required|in:masyarakat,pns',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $userId = $request->tipe_user == 'masyarakat'
            ? $request->id_masyarakat
            : $request->id_pns;

        $penarikans = Penarikan::where(function ($query) use ($request, $userId) {
            if ($request->tipe_user == 'masyarakat') {
                $query->where('id_masyarakat', $userId);
            } else {
                $query->where('id_pns', $userId);
            }
        })
            ->orderBy('tanggal_penarikan', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id_penarikan' => $p->id_penarikan,
                    'tanggal' => $p->tanggal_penarikan ? $p->tanggal_penarikan->format('l, d F Y') : '-',
                    'nominal' => 'Rp ' . number_format($p->jumlah_uang, 0, ',', '.'),
                    'metode' => $p->jenis_ewallet,
                    'nomor_ewallet' => $p->nomor_ewallet,
                    'status' => ucfirst($p->status),
                    'tanggal_penarikan' => $p->tanggal_penarikan,
                    'tanggal_disetujui' => $p->tanggal_disetujui,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $penarikans
        ]);
    }
}
