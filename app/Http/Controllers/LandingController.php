<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Tampilkan landing page dashboard umum
     */
    public function index()
    {
        // Nanti bisa tambah data dinamis dari database
        // Contoh: $features = Feature::all();

        return view('landing.index');
    }
}
