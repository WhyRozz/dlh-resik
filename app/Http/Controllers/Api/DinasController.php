<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dinas;
use Illuminate\Http\Request;

class DinasController extends Controller
{
    public function index()
    {
        $dinas = Dinas::orderBy('nama_dinas', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'data' => $dinas,
            'message' => 'Data dinas berhasil diambil'
        ]);
    }
}
