<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Models\TransaksiSetor;
use App\Models\JenisSampah;
use App\Models\Masyarakat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SetorController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Gunakan 'tanggal_transaksi' BUKAN 'created_at'
        $query = TransaksiSetor::with(['masyarakat', 'jenisSampah', 'petugas'])
            ->orderBy('tanggal_transaksi', 'desc');

        // Filter Pencarian
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->whereHas('masyarakat', function($q) use ($keyword) {
                $q->where('nama_lengkap', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        // Filter Jenis Sampah
        if ($request->filled('jenis_sampah')) {
            $query->where('id_jenis_sampah', $request->jenis_sampah);
        }

        // Filter Tanggal - ✅ Gunakan 'tanggal_transaksi'
        if ($request->filled('tanggal_from')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_from);
        }
        if ($request->filled('tanggal_to')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_to);
        }

        $setorData = $query->paginate(15);

        // Statistik
        $totalSetor   = TransaksiSetor::count();
        $totalBerat   = TransaksiSetor::sum('berat');
        $totalNilai   = TransaksiSetor::sum('total_rupiah');
        $totalNasabah = TransaksiSetor::distinct('id_masyarakat')->count('id_masyarakat');

        $jenisSampah = JenisSampah::all();

        return view('admin.bank-sampah.setor-sampah.index', compact(
            'setorData', 
            'totalSetor', 
            'totalBerat', 
            'totalNilai', 
            'totalNasabah', 
            'jenisSampah'
        ));
    }

    public function detail($id): JsonResponse
    {
        $data = TransaksiSetor::with(['masyarakat', 'jenisSampah', 'petugas'])
            ->findOrFail($id);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_masyarakat' => 'required|exists:masyarakat,id',
            'id_jenis_sampah' => 'required|exists:jenis_sampah,id',
            'berat' => 'required|numeric|min:0.1',
        ]);

        $jenis = JenisSampah::find($validated['id_jenis_sampah']);
        $harga_per_kg = $jenis->harga;
        $total = $validated['berat'] * $harga_per_kg;

        // ✅ Simpan dengan kolom yang benar
        TransaksiSetor::create([
            'id_masyarakat'   => $validated['id_masyarakat'],
            'id_jenis_sampah' => $validated['id_jenis_sampah'],
            'id_petugas'      => auth()->id(),
            'berat'           => $validated['berat'],
            'harga_per_kg'    => $harga_per_kg,
            'total_rupiah'    => $total,
            'tanggal_transaksi' => now(), // ✅ Kolom yang ada di database
        ]);

        return redirect()->back()->with('success', '✅ Data setoran berhasil disimpan!');
    }
}