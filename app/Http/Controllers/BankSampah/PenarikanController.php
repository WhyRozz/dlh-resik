<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Masyarakat;
use App\Models\Pns;
use App\Models\Kecamatan;
use App\Models\Desa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PenarikanExport;
use App\Events\NewWithdrawalRequest;
use Illuminate\Support\Facades\Log;

class PenarikanController extends Controller
{
    /**
     * Tampilkan daftar penarikan (Admin)
     */

    public function index(Request $request)  // ✅ Pastikan ada $request
    {
        // ✅ LANGKAH 1: Ubah jadi $query dulu (JANGAN langsung paginate)
        $query = Penarikan::with(['masyarakat.desa.kecamatan', 'pns.desa.kecamatan']);

        // ✅ LANGKAH 2: ➕ TAMBAH FILTER DI SINI (setelah $query, sebelum paginate)
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_penarikan', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_penarikan', $request->tahun);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Kecamatan
        if ($request->filled('kecamatan_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('masyarakat.desa', function ($sub) use ($request) {
                    $sub->where('id_kecamatan', $request->kecamatan_id);
                })->orWhereHas('pns.desa', function ($sub) use ($request) {
                    $sub->where('id_kecamatan', $request->kecamatan_id);
                });
            });
        }

        // Filter Desa
        if ($request->filled('desa_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('masyarakat.desa', function ($sub) use ($request) {
                    $sub->where('id_desa', $request->desa_id);
                })->orWhereHas('pns.desa', function ($sub) use ($request) {
                    $sub->where('id_desa', $request->desa_id);
                });
            });
        }

        // ✅ LANGKAH 3: Baru paginate setelah filter
        $penarikans = $query->orderBy('tanggal_penarikan', 'desc')->paginate(15);

        // ✅ LANGKAH 4: ➕ Ambil tahun list (sebelum return)
        $tahunList = collect(range(date('Y') - 5, date('Y') + 5));

        // Data untuk filter wilayah
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        $desas = collect();
        if ($request->filled('kecamatan_id')) {
            $desas = Desa::where('id_kecamatan', $request->kecamatan_id)
                ->orderBy('nama_desa')
                ->get();
        }

        // ✅(UNTUK NAMA USER)
        foreach ($penarikans as $penarikan) {
            if ($penarikan->id_masyarakat) {
                $penarikan->nama_user = $penarikan->masyarakat->nama ?? 'Unknown';
            } else {
                $penarikan->nama_user = $penarikan->pns->nama ?? 'Unknown';
            }
        }

        // ✅ LANGKAH 5: ➕ Tambah 'tahunList' di compact
        return view('admin.bank-sampah.penarikan.index', compact('penarikans', 'tahunList', 'kecamatans', 'desas'));
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

            // 2. ✅ BUAT PENARIKAN & SIMPAN KE VARIABLE $penarikan
            $penarikan = Penarikan::create([
                'id_masyarakat'     => $idMasyarakat,
                'id_pns'            => $idPns,
                'jumlah_uang'       => $jumlah,
                'jenis_ewallet'     => $validated['jenis_ewallet'],
                'nomor_ewallet'     => $validated['nomor_ewallet'],
                'status'            => 'diproses',
                'tanggal_penarikan' => now(),
            ]);

            Log::info('Penarikan created:', ['id' => $penarikan->id_penarikan]);

            DB::commit();

            Log::info('About to broadcast event...');

            // 3. ✅ BROADCAST EVENT (setelah commit agar data pasti tersimpan)
            event(new NewWithdrawalRequest($penarikan));

            Log::info('Event broadcasted!');

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
            'alasan_penolakan' => 'nullable|string|max:255',
            'status' => 'required|in:diproses,berhasil,ditolak',
        ]);

        $penarikan = Penarikan::findOrFail($id);
        $statusBaru = $request->status;
        $statusLama = $penarikan->status;

        if ($statusLama === 'berhasil') {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan sudah disetujui dan tidak bisa diubah'
            ], 422);
        }

        if ($statusBaru === 'ditolak' && empty($request->alasan_penolakan)) {
            return response()->json([
                'success' => false,
                'message' => '❌ Alasan penolakan wajib diisi!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            if ($statusBaru === 'ditolak') {
                if ($penarikan->id_masyarakat) {
                    Masyarakat::where('id_masyarakat', $penarikan->id_masyarakat)
                        ->increment('saldo', $penarikan->jumlah_uang);
                } else {
                    Pns::where('id_pns', $penarikan->id_pns)
                        ->increment('saldo', $penarikan->jumlah_uang);
                }
                $message = '❌ Penarikan ditolak. Saldo Rp ' . number_format($penarikan->jumlah_uang, 0, ',', '.') . ' dikembalikan.';
            } elseif ($statusBaru === 'berhasil') {
                $message = '✅ Penarikan disetujui. Silakan transfer ke rekening anggota.';
            } else {
                $message = '🔄 Status diupdate.';
            }

            $penarikan->update([
                'status' => $statusBaru,
                'alasan_penolakan' => $request->alasan_penolakan,
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
    } // ← ✅ updateStatus() DITUTUP DI SINI

    /**
     * ✅ Export to Excel - METHOD TERPISAH
     */
    public function export(Request $request)
    {
        $filter = [];

        if ($request->filled('bulan')) {
            $filter['bulan'] = $request->bulan;
        }
        if ($request->filled('tahun')) {
            $filter['tahun'] = $request->tahun;
        }
        if ($request->filled('status')) {
            $filter['status'] = $request->status;
        }

        $filename = 'Data_Penarikan_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new PenarikanExport($filter), $filename);
    }
}
