<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenarikanController extends Controller
{
    // 1. Tampilkan daftar
    public function index()
    {
        $penarikans = Penarikan::orderBy('tanggal_penarikan', 'desc')->get();
        return view('admin.bank-sampah.penarikan.index', compact('penarikans'));
    }

    // 2. Detail (untuk AJAX modal)
    public function show($id)
    {
        $penarikan = Penarikan::with('masyarakat')->findOrFail($id);
        return response()->json($penarikan);
    }

    // 3. Simpan data baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_masyarakat' => 'required|exists:masyarakats,id',
            'jumlah_uang'   => 'required|numeric|min:1000',
            'jenis_ewallet' => 'required|string|max:50',
            'nomor_ewallet' => 'required|string|max:50',
        ]);

        Penarikan::create([
            'id_masyarakat' => $validated['id_masyarakat'],
            'id_pns'        => Auth::id() ?? null,
            'jumlah_uang'   => $validated['jumlah_uang'],
            'jenis_ewallet' => $validated['jenis_ewallet'],
            'nomor_ewallet' => $validated['nomor_ewallet'],
            'status'        => 'diproses',
            'tanggal_penarikan' => now(),
        ]);

        return redirect()->route('admin.bank-sampah.tarik.index')
                         ->with('success', 'Data penarikan berhasil ditambahkan.');
    }

    // 4. Update status (AJAX)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:diproses,berhasil,ditolak'
        ]);

        $penarikan = Penarikan::findOrFail($id);
        $penarikan->update(['status' => $request->status]);

        return response()->json(['message' => 'Status berhasil diupdate']);
    }

    // 5. Hapus data
    public function destroy($id)
    {
        Penarikan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}