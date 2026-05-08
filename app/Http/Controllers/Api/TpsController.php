<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Tps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TpsController extends Controller
{
    /**
     * GET /api/tps
     * List semua TPS dengan filter & search
     */
    public function index(Request $request)
    {
        try {
            $query = Tps::query();

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama_tps', 'LIKE', "%{$search}%")
                        ->orWhere('alamat', 'LIKE', "%{$search}%");
                });
            }

            $tpsList = $query->get()->map(function ($tps) {
                return [
                    'id' => (int) $tps->id_tps,
                    'nama' => $tps->nama_tps,
                    'lokasi' => $tps->lokasi ?? '-',
                    'alamat' => $tps->alamat ?? '-',
                    'kapasitas' => $tps->kapasitas ?? '-',
                    'keterangan' => $tps->keterangan,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $tpsList,
                'total' => $tpsList->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $tps = Tps::where('id_tps', $id)->first();

            if (!$tps) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'TPS tidak ditemukan'
                ], 404);
            }

            // ✅ Parse koordinat dari field 'lokasi' (format: "-7.65492,111.97055")
            $latitude = null;
            $longitude = null;

            if ($tps->lokasi && strpos($tps->lokasi, ',') !== false) {
                $coords = explode(',', $tps->lokasi);
                $latitude = (float) trim($coords[0]);
                $longitude = (float) trim($coords[1]);
            }

            $mapsQuery = urlencode($tps->lokasi ?? $tps->alamat ?? $tps->nama_tps);

            $data = [
                'id' => (int) $tps->id_tps,
                'nama_tps' => $tps->nama_tps,
                'lokasi' => $tps->lokasi ?? '-',
                'alamat' => $tps->alamat ?? '-',
                'kapasitas' => $tps->kapasitas ?? '-',
                'keterangan' => $tps->keterangan,

                // ✅ Koordinat untuk embedded map
                'latitude' => $latitude,
                'longitude' => $longitude,

                // ✅ Google Maps URL untuk buka app
                'maps_url' => "https://www.google.com/maps/search/?api=1&query={$mapsQuery}",
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
