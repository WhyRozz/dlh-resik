<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PetugasController extends Controller
{
    /**
     * Simpan petugas baru (AJAX - RETURN JSON)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:100',
                'email' => 'required|email|max:150|unique:petugas,email',
                'password' => 'required|string|min:8|max:50', // ✅ TANPA REGEX - boleh karakter spesial
                'no_telepon' => 'required|string|max:15',
                'level' => 'required|in:petugas_dlh,bank_sampah',
            ], [
                'email.unique' => 'Email sudah terdaftar.',
                'level.in' => 'Pilih level petugas yang valid.',
            ]);

            $hashedPassword = Hash::make($validated['password']);
            $encryptedPassword = EncryptionService::encrypt($validated['password']);

            Petugas::create([
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'password' => $hashedPassword,
                'password_encrypted' => $encryptedPassword,
                'no_telepon' => $validated['no_telepon'],
                'level' => $validated['level'],
                'is_active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun petugas berhasil ditambahkan.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error create petugas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update petugas (AJAX - RETURN JSON)
     */
    public function update(Request $request, $id)
    {
        try {
            $petugas = Petugas::findOrFail($id);

            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:100',
                'email' => 'required|email|max:150|unique:petugas,email,' . $id . ',id_petugas',
                'password' => 'nullable|string|min:8|max:50', // ✅ TANPA REGEX - boleh karakter spesial
                'no_telepon' => 'required|string|max:15',
                'level' => 'required|in:petugas_dlh,bank_sampah',
            ], [
                'email.unique' => 'Email sudah terdaftar.',
                'level.in' => 'Pilih level petugas yang valid.',
            ]);

            $updateData = [
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'no_telepon' => $validated['no_telepon'],
                'level' => $validated['level'],
            ];

            // Update password hanya jika diisi
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
                $updateData['password_encrypted'] = EncryptionService::encrypt($validated['password']);
            }

            $petugas->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Akun petugas berhasil diperbarui.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error update petugas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus petugas (AJAX - RETURN JSON)
     */
    public function destroy($id)
    {
        try {
            $petugas = Petugas::findOrFail($id);
            $petugas->delete();

            return response()->json([
                'success' => true,
                'message' => 'Akun petugas berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error delete petugas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data petugas untuk edit (JSON)
     */
    public function show($id)
    {
        try {
            $petugas = Petugas::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id_petugas' => $petugas->id_petugas,
                    'nama_lengkap' => $petugas->nama_lengkap,
                    'email' => $petugas->email,
                    'no_telepon' => $petugas->no_telepon,
                    'level' => $petugas->level,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }
}