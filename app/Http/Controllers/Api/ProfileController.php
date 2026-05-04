<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use App\Models\Pns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Update profile user
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'tipe' => 'required|in:masyarakat,pns',
            'nama' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:15',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $userId = $request->user_id;
        $tipe = $request->tipe;

        try {
            if ($tipe == 'masyarakat') {
                $user = Masyarakat::find($userId);

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User tidak ditemukan'
                    ], 404);
                }

                $user->update([
                    'nama' => $request->nama,
                    'no_telepon' => $request->no_telepon,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'alamat' => $request->alamat,
                ]);

            } elseif ($tipe == 'pns') {
                $user = Pns::find($userId);

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User tidak ditemukan'
                    ], 404);
                }

                $user->update([
                    'nama' => $request->nama,
                    'no_telepon' => $request->no_telepon,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'alamat' => $request->alamat,
                ]);

            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipe user tidak valid'
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'data' => [
                    'user' => $user
                ],
                'message' => 'Profil berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal update profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get profile user by ID
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'tipe' => 'required|in:masyarakat,pns',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $userId = $request->user_id;
        $tipe = $request->tipe;

        try {
            if ($tipe == 'masyarakat') {
                $user = Masyarakat::find($userId);
            } elseif ($tipe == 'pns') {
                $user = Pns::with('dinas')->find($userId);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipe user tidak valid'
                ], 422);
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'data' => [
                    'user' => $user
                ],
                'message' => 'Data profil berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
