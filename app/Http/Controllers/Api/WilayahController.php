<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\Desa;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    // GET /api/kecamatans
    public function kecamatans()
    {
        $data = Kecamatan::select('id_kecamatan', 'nama_kecamatan')
            ->orderBy('nama_kecamatan')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    // GET /api/desas?kecamatan_id=X
    public function desas(Request $request)
    {
        $query = Desa::select('id_desa', 'id_kecamatan', 'nama_desa');

        if ($request->filled('kecamatan_id')) {
            $query->where('id_kecamatan', $request->kecamatan_id);
        }

        $data = $query->orderBy('nama_desa')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}