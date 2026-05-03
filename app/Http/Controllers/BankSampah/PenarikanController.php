<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use Illuminate\Http\Request;

class PenarikanController extends Controller
{
    /**
     * Tampilkan daftar penarikan
     */
    public function index()
    {
        $penarikans = Penarikan::latest()->get();
        return view('bank-sampah.penarikan.index', compact('penarikans'));
    }

    /**
     * Tampilkan detail penarikan
     */
    public function show($id)
    {
        $penarikan = Penarikan::findOrFail($id);
        return response()->json($penarikan);
    }

    /**
     * Update status penarikan
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Diproses,Diterima,Ditolak',
        ]);

        $penarikan = Penarikan::findOrFail($id);
        $penarikan->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Status berhasil diupdate');
    }

    /**
     * Hapus penarikan
     */
    public function destroy($id)
    {
        $penarikan = Penarikan::findOrFail($id);
        $penarikan->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }
}