<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use Illuminate\Http\Request;

class DesaController extends Controller
{
    /**
     * GET /api/desa
     * Return semua desa, atau filter by kecamatan_id
     */
    public function index(Request $request)
    {
        try {
            $query = Desa::query();

            // ✅ Filter by kecamatan_id jika ada parameter
            if ($request->has('kecamatan_id') && $request->kecamatan_id) {
                $query->where('id_kecamatan', $request->kecamatan_id);
            }

            $desaList = $query->orderBy('nama_desa', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $desaList->map(function($d) {
                    return [
                        'id_desa' => $d->id_desa,
                        'nama_desa' => $d->nama_desa,
                        'jenis' => $d->jenis,
                        'kode_desa' => $d->kode_desa,
                        'id_kecamatan' => $d->id_kecamatan,
                        // Optional: include nama kecamatan
                        'nama_kecamatan' => $d->kecamatan ? $d->kecamatan->nama_kecamatan : null,
                    ];
                }),
                'total' => $desaList->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data desa: ' . $e->getMessage()
            ], 500);
        }
    }
}
