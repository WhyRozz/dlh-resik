<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Masyarakat;
use App\Models\Pns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenarikanController extends Controller
{
    /**
     * Tampilkan daftar penarikan (Admin)
     */

    public function index()
    {
        $penarikans = Penarikan::with(['masyarakat', 'pns'])
            ->orderBy('tanggal_penarikan', 'desc')
            ->paginate(15);

        return view('admin.bank-sampah.penarikan.index', compact('penarikans'));
    }

    /**
     * User ajukan penarikan (Masyarakat/PNS)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jumlah_uang'   => 'required|numeric|min:1000',
            'jenis_ewallet' => 'required|string|max:50',
            'nomor_ewallet' => 'required|string|max:50',
        ]);

        // Deteksi user yang login
        $user = Auth::guard('masyarakat')->user() ?? Auth::guard('pns')->user();
        $guard = Auth::guard('masyarakat')->check() ? 'masyarakat' : 'pns';

        $jumlah = $validated['jumlah_uang'];

        // Cek saldo
        if ($user->saldo < $jumlah) {
            return redirect()->back()->with('error', '❌ Saldo tidak mencukupi. Saldo Anda: Rp ' . number_format($user->saldo, 0, ',', '.'));
        }

        DB::beginTransaction();
        try {
            // 1. Potong saldo
            $saldoBaru = $user->saldo - $jumlah;

            if ($guard === 'masyarakat') {
                Masyarakat::where('id_masyarakat', $user->id_masyarakat)->update(['saldo' => $saldoBaru]);
                $idMasyarakat = $user->id_masyarakat;
                $idPns = null;
            } else {
                Pns::where('id_pns', $user->id_pns)->update(['saldo' => $saldoBaru]);
                $idMasyarakat = null;
                $idPns = $user->id_pns;
            }

            // 2. Buat penarikan
            Penarikan::create([
                'id_masyarakat'     => $idMasyarakat,
                'id_pns'            => $idPns,
                'jumlah_uang'       => $jumlah,
                'jenis_ewallet'     => $validated['jenis_ewallet'],
                'nomor_ewallet'     => $validated['nomor_ewallet'],
                'status'            => 'diproses',
                'tanggal_penarikan' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', '✅ Penarikan berhasil diajukan! Saldo terpotong Rp ' . number_format($jumlah, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal mengajukan penarikan: ' . $e->getMessage());
        }
    }

    /**
     * Detail penarikan (AJAX)
     */
    public function show($id)
    {
        $penarikan = Penarikan::with(['masyarakat', 'pns'])->findOrFail($id);

        $userName = $penarikan->masyarakat->nama ?? $penarikan->pns->nama ?? 'Unknown';

        // ✅ Gabungkan array dengan benar menggunakan array_merge
        return response()->json(
            array_merge($penarikan->toArray(), ['nama_user' => $userName])
        );
    }

    /**
     * Admin update status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:diproses,berhasil,ditolak',
        ]);

        $penarikan = Penarikan::findOrFail($id);
        $statusBaru = $request->status;
        $statusLama = $penarikan->status;

        // Tidak boleh ubah yang sudah berhasil
        if ($statusLama === 'berhasil') {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan sudah disetujui dan tidak bisa diubah'
            ], 422);
        }

        DB::beginTransaction();
        try {
            if ($statusBaru === 'ditolak') {
                // === DITOLAK: KEMBALIKAN SALDO ===
                // Langsung increment saldo tanpa perlu ambil data user dulu
                if ($penarikan->id_masyarakat) {
                    Masyarakat::where('id_masyarakat', $penarikan->id_masyarakat)
                        ->increment('saldo', $penarikan->jumlah_uang);
                } else {
                    Pns::where('id_pns', $penarikan->id_pns)
                        ->increment('saldo', $penarikan->jumlah_uang);
                }

                $message = '❌ Penarikan ditolak. Saldo Rp ' . number_format($penarikan->jumlah_uang, 0, ',', '.') . ' dikembalikan.';
            } elseif ($statusBaru === 'berhasil') {
                // === BERHASIL: SALDO TETAP TERPOTONG ===
                $message = '✅ Penarikan disetujui. Silakan transfer ke rekening anggota.';
            } else {
                $message = '🔄 Status diupdate.';
            }

            // Update data penarikan
            $penarikan->update([
                'status' => $statusBaru,
                'updated_by' => Auth::id(),
                'tanggal_disetujui' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'status_baru' => $statusBaru
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus penarikan
     */
    public function destroy($id)
    {
        $penarikan = Penarikan::findOrFail($id);

        // Tidak boleh hapus jika sudah berhasil
        if ($penarikan->status === 'berhasil') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus penarikan yang sudah disetujui');
        }

        DB::beginTransaction();
        try {
            // Kembalikan saldo jika masih diproses
            if ($penarikan->status === 'diproses') {
                if ($penarikan->id_masyarakat) {
                    $user = Masyarakat::findOrFail($penarikan->id_masyarakat);
                    Masyarakat::where('id_masyarakat', $user->id_masyarakat)
                        ->increment('saldo', $penarikan->jumlah_uang);
                } else {
                    $user = Pns::findOrFail($penarikan->id_pns);
                    Pns::where('id_pns', $user->id_pns)
                        ->increment('saldo', $penarikan->jumlah_uang);
                }
            }

            $penarikan->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Data penarikan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
