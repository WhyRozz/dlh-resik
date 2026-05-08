<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use App\Models\Pns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        \Log::info('=== PROFILE UPDATE DIPANGGIL ===');
        \Log::info('Request: ' . json_encode($request->all()));

        // ✅ Validasi
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'tipe' => 'required|in:masyarakat,pns',
            'nama' => 'required|string|max:100',
            'no_telepon' => 'nullable|string|max:15',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = $request->user_id;
            $tipe = $request->tipe;

            // ✅ Cari user berdasarkan tipe
            if ($tipe === 'masyarakat') {
                $user = Masyarakat::where('id_masyarakat', $userId)->first();
            } else {
                $user = Pns::where('id_pns', $userId)->first();
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // ✅ Handle upload foto jika ada
            // ✅ LOGIC URL FOTO YANG AMAN (tidak akan dobel)
            $fotoUrl = null;

            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($user->foto && !str_starts_with($user->foto, 'http')) {
                    if (Storage::disk('public')->exists($user->foto)) {
                        Storage::disk('public')->delete($user->foto);
                    }
                }

                // Upload foto baru → simpan path relatif saja
                $path = $request->file('foto')->store('profile', 'public');
                $fotoUrl = asset('storage/' . $path); // ✅ Jadikan URL lengkap DI SINI
            }

            // Update data user
            $updateData = [
                'nama' => $request->nama,
                'no_telepon' => $request->no_telepon,
                'tanggal_lahir' => $request->tanggal_lahir ?: null,
                'alamat' => $request->alamat ?: null,
            ];

            // Tambahkan foto jika ada upload baru
            if ($fotoUrl) {
                $updateData['foto'] = $fotoUrl; // ✅ Simpan URL lengkap ke DB
            }

            $user->update($updateData);

            // ✅ RESPONSE: Kirim URL yang sudah jadi (jangan di-asset() lagi!)
            return response()->json([
                'status' => 'success',
                'message' => 'Profil berhasil diupdate',
                'data' => [
                    'id_masyarakat' => $user->id_masyarakat ?? null,
                    'id_pns' => $user->id_pns ?? null,
                    'nama' => $user->nama,
                    'email' => $user->email ?? '',
                    'no_telepon' => $user->no_telepon,
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'alamat' => $user->alamat,

                    // ✅ PASTIKAN TIDAK DOBEL:
                    'foto' => $fotoUrl ?? $user->foto, // ← Jangan pakai asset() lagi di sini!

                    'tipe' => $tipe,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Profile Update Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
