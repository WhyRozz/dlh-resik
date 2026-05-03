<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    /**
     * Tampilkan halaman kelola laporan
     */
    public function index()
    {
        $laporanList = Laporan::with('masyarakat')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.laporan.index', compact('laporanList'));
    }

    /**
     * Update status laporan via AJAX
     */
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:laporan,id',
            'status' => 'required|in:Diproses,Diterima,Ditolak',
            'balasan' => 'nullable|string|max:500',
        ]);

        try {
            $laporan = Laporan::findOrFail($validated['id']);

            // Validasi: hanya bisa update jika status masih Diproses
            if ($laporan->status !== 'Diproses' && $validated['status'] === 'Diproses') {
                return response()->json(['success' => false, 'message' => 'Status tidak dapat diubah kembali ke Diproses'], 400);
            }

            $laporan->update([
                'status' => $validated['status'],
                'balasan' => $validated['balasan'] ?: null,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
