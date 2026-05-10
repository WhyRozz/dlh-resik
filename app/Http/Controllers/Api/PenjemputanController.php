<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjemputan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenjemputanController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_petugas' => 'required|integer|exists:petugas,id_petugas',
            'nama_admin' => 'required|string',
            'waktu' => 'required|date',
            'lokasi' => 'required|string',
            'berat' => 'required|numeric|min:0.1',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|in:diproses,disetujui,ditolak',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $data = [
                'id_petugas' => $request->id_petugas,
                'nama_admin' => $request->nama_admin,
                'waktu' => $request->waktu,
                'lokasi' => $request->lokasi,
                'berat' => $request->berat,
                'keterangan' => $request->keterangan,
                'status' => $request->status ?? 'diproses',
            ];

            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('penjemputan', 'public');
                $data['foto'] = $fotoPath;
            }

            $penjemputan = Penjemputan::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Penjemputan berhasil diajukan',
                'data' => $penjemputan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengajukan penjemputan: ' . $e->getMessage()
            ], 500);
        }
    }
    }

