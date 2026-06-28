<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    /**
     * GET /api/kecamatan
     * Return semua kecamatan
     */
    public function index()
    {
        try {
            $kecamatanList = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $kecamatanList->map(function($k) {
                    return [
                        'id_kecamatan' => $k->id_kecamatan,
                        'nama_kecamatan' => $k->nama_kecamatan,
                        'kode_kecamatan' => $k->kode_kecamatan,
                    ];
                }),
                'total' => $kecamatanList->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data kecamatan: ' . $e->getMessage()
            ], 500);
        }
    }
}
