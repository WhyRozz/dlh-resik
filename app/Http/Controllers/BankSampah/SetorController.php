<?php
namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Models\TransaksiSetor;
use App\Models\JenisSampah;
use App\Models\Masyarakat;
use App\Models\Pns;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SetorController extends Controller
{
    public function index(Request $request)
    {
        $query = TransaksiSetor::with(['masyarakat', 'pns', 'jenisSampah', 'petugas'])
            ->orderBy('tanggal_transaksi', 'desc');

        // Filter Pencarian
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function($q) use ($keyword) {
                // Cari berdasarkan NAMA (Masyarakat / PNS / Petugas)
                $q->whereHas('masyarakat', function($sub) use ($keyword) {
                        $sub->where('nama', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('pns', function($sub) use ($keyword) {
                        $sub->where('nama', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('petugas', function($sub) use ($keyword) {
                        $sub->where('nama_lengkap', 'like', '%' . $keyword . '%');
                    })
                    // ✅ TAMBAHAN: Cari berdasarkan JENIS SAMPAH
                    ->orWhereHas('jenisSampah', function($sub) use ($keyword) {
                        $sub->where('jenis', 'like', '%' . $keyword . '%');
                    });
            });
        
        // ✅ Filter Bulan
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_transaksi', $request->bulan);
        }

        // ✅ Filter Tahun
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_transaksi', $request->tahun);
        }
    }

        $setorData = $query->paginate(15);

        // Statistik
        $totalSetor   = TransaksiSetor::count();
        $totalBerat   = TransaksiSetor::sum('berat') ?? 0;
        $totalNilai   = TransaksiSetor::sum('total_rupiah') ?? 0;
        
        // Hitung nasabah unik
        $totalNasabah = DB::table('transaksi_setor')
            ->select(DB::raw('COUNT(DISTINCT id_masyarakat) + COUNT(DISTINCT id_pns) as total'))
            ->value('total') ?? 0;

        $jenisSampah = JenisSampah::all();

        // ✅ AJAX RESPONSE (TANPA FILE TAMBAHAN)
        if ($request->ajax()) {
            return response()->json([
                'table' => view('admin.bank-sampah.setor-sampah.index', compact('setorData', 'totalSetor', 'totalBerat', 'totalNilai', 'totalNasabah', 'jenisSampah'))->render()
            ]);
        }

        return view('admin.bank-sampah.setor-sampah.index', compact(
            'setorData', 'totalSetor', 'totalBerat', 'totalNilai', 'totalNasabah', 'jenisSampah'
        ));
    }

    public function detail($id): JsonResponse
    {
        $data = TransaksiSetor::with(['masyarakat', 'pns', 'jenisSampah', 'petugas'])
            ->findOrFail($id);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_masyarakat'   => 'nullable|exists:masyarakat,id_masyarakat',
            'id_pns'          => 'nullable|exists:pns,id_pns',
            'id_jenis_sampah' => 'required|exists:jenis_sampah,id_jenis_sampah',
            'berat'           => 'required|numeric|min:0.1',
        ]);

        // Validasi: Harus ada salah satu
        if (empty($validated['id_masyarakat']) && empty($validated['id_pns'])) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan!');
        }

        $jenis = JenisSampah::find($validated['id_jenis_sampah']);
        $harga_per_kg = $jenis->harga;
        $total = $validated['berat'] * $harga_per_kg;

        // ✅ Dapatkan ID petugas yang benar
        $idPetugas = auth()->user()->id_petugas ?? auth()->id();

        DB::beginTransaction();
        try {
            // Buat transaksi
            TransaksiSetor::create([
                'id_masyarakat'   => $validated['id_masyarakat'] ?? null,
                'id_pns'          => $validated['id_pns'] ?? null,
                'id_jenis_sampah' => $validated['id_jenis_sampah'],
                'id_petugas'      => $idPetugas,
                'berat'           => $validated['berat'],
                'harga_per_kg'    => $harga_per_kg,
                'total_rupiah'    => $total,
                'tanggal_transaksi' => now(),
            ]);

            // ✅ Update saldo (jika model boot() tidak jalan, ini backup)
            if ($validated['id_masyarakat']) {
                Masyarakat::where('id_masyarakat', $validated['id_masyarakat'])
                    ->increment('saldo', $total);
            }
            if ($validated['id_pns']) {
                Pns::where('id_pns', $validated['id_pns'])
                    ->increment('saldo', $total);
            }

            DB::commit();
            return redirect()->back()->with('success', '✅ Data setoran berhasil disimpan! Saldo pengguna telah ditambahkan.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}