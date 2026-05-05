<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index()
    {
        $artikels = Artikel::orderBy('tanggal', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $artikels
        ]);
    }
}
