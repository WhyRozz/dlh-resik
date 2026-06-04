<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TpsController extends Controller
{
    /**
     * Tampilkan daftar TPS
     */
    public function index()
    {
        $tpsList = Tps::orderBy('nama_tps', 'asc')->get();
        return view('admin.tps.index', compact('tpsList'));
    }

    /**
     * Tampilkan form tambah TPS
     */
    public function create()
    {
        return view('admin.tps.form');
    }

    /**
     * Tampilkan form edit TPS
     */
    public function edit($id)
    {
        $tps = Tps::findOrFail($id);
        return view('admin.tps.form', compact('tps'));
    }

    /**
     * Simpan TPS baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tps' => 'required|string|max:150',
            'lokasi' => [
                'required',
                'string',
                'max:255',
                'regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/',
            ],
            'alamat' => 'required|string',
            'kapasitas' => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
        ], [
            'nama_tps.required' => 'Nama TPS wajib diisi.',
            'lokasi.required' => 'Koordinat GPS wajib diisi.',
            'lokasi.regex' => 'Format koordinat tidak valid. Gunakan titik koordinat yang benar',
            'alamat.required' => 'Alamat Lengkap wajib diisi.',
            'kapasitas.string' => 'Kapasitas harus berupa angka & teks.',
        ]);

        Tps::create($validated);

        return redirect()->route('admin.tps.index')
            ->with('success', 'Data TPS berhasil ditambahkan!');
    }

    /**
     * Update TPS
     */
    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'nama_tps' => 'required|string|max:150',
        'lokasi'   => 'required|string|max:255|regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/',
        'alamat'   => 'required|string',
        'kapasitas' => 'nullable|string|max:20', // DI DATABASE VARCHAR, JANGAN PAKE INTEGER
        'keterangan'=> 'nullable|string',
    ], [
        'nama_tps.required' => 'Nama TPS wajib diisi.',
        'lokasi.required'   => 'Lokasi koordinat wajib diisi.',
        'lokasi.regex' => 'Format koordinat tidak valid. Gunakan titik koordinat yang benar',
        'alamat.required'   => 'Alamat wajib diisi.',
    ]);

    $tps = Tps::findOrFail($id);
    $tps->update($validated);

    return redirect()->route('admin.tps.index')->with('success', 'Data TPS berhasil diperbarui!');
}

/**
 * Hapus TPS via AJAX
 */
public function destroy($id)
{
    try {
        $tps = Tps::findOrFail($id);
        $tps->delete();
        
        // ✅ Return JSON untuk AJAX
        return response()->json([
            'success' => true,
            'message' => 'Data TPS berhasil dihapus!'
        ]);
        
    } catch (\Exception $e) {
        // ✅ Return error JSON
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus: ' . $e->getMessage()
        ], 500);
    }
}
}