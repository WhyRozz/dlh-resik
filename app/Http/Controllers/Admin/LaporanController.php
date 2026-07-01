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
    public function index(Request $request)
    {
        $laporanList = Laporan::with('masyarakat')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

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
            'foto_balasan' => 'nullable|image|max:10240', // ← MAKSIMAL 10MB (10240 KB)
        ], [
            'foto_balasan.image' => 'File harus berupa gambar.',
            'foto_balasan.max' => 'Ukuran gambar maksimal 10MB.',
        ]);

        try {
            $laporan = Laporan::findOrFail($validated['id']);

            // Validasi: hanya bisa update jika status masih Diproses
            if ($laporan->status !== 'Diproses' && $validated['status'] === 'Diproses') {
                return response()->json(['success' => false, 'message' => 'Status tidak dapat diubah kembali ke Diproses'], 400);
            }

            $updateData = [
                'status' => $validated['status'],
                'balasan' => $validated['balasan'] ?: null,
            ];

            // ✅ Handle upload foto balasan
            if ($request->hasFile('foto_balasan')) {
                // Hapus foto lama jika ada
                if ($laporan->foto_balasan && Storage::disk('public')->exists($laporan->foto_balasan)) {
                    Storage::disk('public')->delete($laporan->foto_balasan);
                }

                $file = $request->file('foto_balasan');
                $filename = time() . '_balasan_' . $laporan->id . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('laporan/balasan', $filename, 'public');

                $updateData['foto_balasan'] = $path;
            }

            $laporan->update($updateData);

            $laporan->refresh();

            $notification = new \App\Services\NotificationService();

            $user = $laporan->masyarakat ?? $laporan->pns;

            $notification->sendToUser(
                $user?->fcm_token,
                "Status Laporan",
                "Laporan Anda telah {$laporan->status}.",
                [
                    "type" => "report_result",
                    "id" => (string)$laporan->id
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
