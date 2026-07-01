<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Events\NewWithdrawalRequest;
use App\Services\NotificationService;

class PenarikanController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('=== PENARIKAN STORE DIPANGGIL ===');

        $validator = Validator::make($request->all(), [
            'id_masyarakat' => 'nullable|integer|exists:masyarakat,id_masyarakat',
            'id_pns' => 'nullable|integer|exists:pns,id_pns',
            'tipe_user' => 'required|in:masyarakat,pns',
            'nama' => 'required|string|max:100',
            'jenis_layanan' => 'required|in:e-wallet,bank',
            'jenis_ewallet' => 'nullable|required_if:jenis_layanan,e-wallet|in:Dana,OVO,GoPay,ShopeePay',
            'nama_bank' => 'nullable|required_if:jenis_layanan,bank|in:Bank BCA,Bank BRI,Bank Mandiri,Bank Jatim',
            'nomor_ewallet' => 'required|string|max:20',
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

            $userId = $request->tipe_user === 'masyarakat'
                ? $request->id_masyarakat
                : $request->id_pns;

            if ($request->tipe_user === 'masyarakat') {

                $user = \App\Models\Masyarakat::with('desa')
                    ->find($request->id_masyarakat);
            } else {

                $user = \App\Models\Pns::with('dinas')
                    ->find($request->id_pns);
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            if ($user->saldo < $request->jumlah_uang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Saldo tidak mencukupi'
                ], 422);
            }

            // Kurangi saldo
            $user->saldo -= $request->jumlah_uang;
            $user->save();

            // ✅ Simpan dengan status yang sesuai ENUM database
            $penarikan = Penarikan::create([
                'id_masyarakat' => $request->tipe_user === 'masyarakat' ? $userId : null,
                'id_pns' => $request->tipe_user === 'pns' ? $userId : null,
                'nama' => $request->nama,
                'jenis_layanan' => $request->jenis_layanan ?? 'e-wallet',
                'jenis_ewallet' => $request->jenis_layanan === 'bank' ? null : $request->jenis_ewallet,
                'nama_bank' => $request->jenis_layanan === 'bank' ? $request->nama_bank : null,
                'nomor_ewallet' => $request->nomor_ewallet,
                'jumlah_uang' => $request->jumlah_uang,
                'status' => 'diproses', // ✅ GANTI 'Pending' jadi 'diproses'
                'tanggal_penarikan' => now(),
                'alasan_penolakan' => null,
                'updated_by' => null,
                'tanggal_disetujui' => null,
            ]);

            $notification = new NotificationService();

            if ($request->tipe_user == 'masyarakat') {

                $notification->sendWithdrawal(
                    $user->nama,
                    optional($user->desa)->nama_desa ?? "Tidak diketahui",
                    $user->id_desa,
                    $request->jumlah_uang,
                    $penarikan->id_penarikan
                );
            } else {

                $notification->sendWithdrawal(
                    $user->nama,
                    optional($user->dinas)->nama_dinas ?? "DLH",
                    0,
                    $request->jumlah_uang,
                    $penarikan->id_penarikan
                );
            }

            event(new NewWithdrawalRequest($penarikan));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan penarikan berhasil diajukan',
                'data' => ['id' => $penarikan->id_penarikan]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Penarikan Store Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Method untuk ambil riwayat penarikan user
    public function index(Request $request)
    {
        // ✅ TERIMA PARAMETER SESUAI FLUTTER
        $idMasyarakat = $request->query('id_masyarakat');
        $idPns = $request->query('id_pns');
        $tipeUser = $request->query('tipe_user'); // 'masyarakat' atau 'pns'

        // Validasi: harus ada tipe_user + salah satu ID
        if (!$tipeUser || (!$idMasyarakat && !$idPns)) {
            \Log::error('Parameter tidak lengkap: ' . json_encode($request->query()));
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak lengkap'
            ], 422);
        }

        try {
            $query = Penarikan::query();

            // ✅ Filter berdasarkan tipe user
            if ($tipeUser === 'masyarakat') {
                $query->where('id_masyarakat', $idMasyarakat);
            } elseif ($tipeUser === 'pns') {
                $query->where('id_pns', $idPns);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipe user tidak valid'
                ], 422);
            }

            $penarikans = $query->orderBy('tanggal_penarikan', 'desc')
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id_penarikan,
                        'nama' => $p->nama ?? '-',
                        'jenis_layanan' => $p->jenis_layanan ?? 'e-wallet',  // ✅ TAMBAH INI
                        'nama_bank' => $p->nama_bank,
                        'jenis_ewallet' => $p->jenis_ewallet,
                        'nomor_ewallet' => $p->nomor_ewallet,
                        'jumlah_uang' => (float) $p->jumlah_uang,
                        'status' => $p->status,
                        'tanggal_penarikan' => $p->tanggal_penarikan ? $p->tanggal_penarikan->format('d-m-Y H:i') : null,
                        'alasan_penolakan' => $p->alasan_penolakan,
                        'tanggal_disetujui' => $p->tanggal_disetujui ? $p->tanggal_disetujui->format('d-m-Y H:i') : null,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $penarikans
            ]);
        } catch (\Exception $e) {
            \Log::error('Penarikan Index Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
