<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use App\Models\Pns;
use App\Models\Kecamatan;
use App\Models\Desa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DataPenggunaExport;

class DataPenggunaController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $search = $request->query('search', '');
        $kecamatanId = $request->query('kecamatan_id', '');
        $desaId = $request->query('desa_id', '');
        
        $query = $this->getUsersQuery($filter, $search, $kecamatanId, $desaId);
        
        $users = $query->paginate(15);
        
        // Ambil data kecamatan dan desa untuk dropdown filter
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        $desas = collect();
        
        if ($kecamatanId) {
            $desas = Desa::where('id_kecamatan', $kecamatanId)
                ->orderBy('nama_desa')
                ->get();
        }

        return view('admin.data-pengguna.index', compact(
            'users', 
            'filter', 
            'search', 
            'kecamatans', 
            'desas',
            'kecamatanId',
            'desaId'
        ));
    }

    private function getUsersQuery($filter, $search = '', $kecamatanId = '', $desaId = '')
    {
        if ($filter === 'masyarakat') {
            $query = DB::table('masyarakat')
                ->leftJoin('desa', 'masyarakat.id_desa', '=', 'desa.id_desa')
                ->leftJoin('kecamatan', 'desa.id_kecamatan', '=', 'kecamatan.id_kecamatan')
                ->select(
                    'masyarakat.id_masyarakat as id',
                    'masyarakat.nama',
                    'masyarakat.email',
                    DB::raw("'Masyarakat' as jenis_pengguna"),
                    'masyarakat.no_telepon',            
                    'masyarakat.jenis_kelamin',         
                    'masyarakat.tanggal_lahir',         
                    'masyarakat.foto',                  
                    'masyarakat.saldo',                 
                    DB::raw('NULL as kode_anggota'),
                    DB::raw('NULL as id_dinas'),
                    DB::raw('NULL as nama_dinas'),
                    'desa.nama_desa',
                    'kecamatan.nama_kecamatan',
                    'masyarakat.created_at',
                    'masyarakat.updated_at'
                );

            // Filter search
            if ($search) {
                $query->where('masyarakat.nama', 'LIKE', "%{$search}%");
            }

            // Filter kecamatan
            if ($kecamatanId) {
                $query->where('desa.id_kecamatan', $kecamatanId);
            }

            // Filter desa
            if ($desaId) {
                $query->where('masyarakat.id_desa', $desaId);
            }

            return $query->orderBy('masyarakat.created_at', 'desc');

        } elseif ($filter === 'asn') {
            $query = DB::table('pns')
                ->leftJoin('dinas', 'pns.id_dinas', '=', 'dinas.id_dinas')
                ->select(
                    'pns.id_pns as id',
                    'pns.nama',
                    'pns.email',
                    DB::raw("'PNS' as jenis_pengguna"),
                    'pns.no_telepon',
                    'pns.jenis_kelamin',
                    'pns.tanggal_lahir',
                    'pns.foto',
                    'pns.saldo',
                    'pns.kode_anggota',
                    'pns.id_dinas',
                    'dinas.nama_dinas',
                    DB::raw('NULL as nama_desa'),
                    DB::raw('NULL as nama_kecamatan'),
                    'pns.created_at',
                    'pns.updated_at'
                );

            // Filter search
            if ($search) {
                $query->where('pns.nama', 'LIKE', "%{$search}%");
            }

            return $query->orderBy('pns.created_at', 'desc');

        } else {
            // UNION untuk semua
            
            // Query Masyarakat
            $masyarakatQuery = DB::table('masyarakat')
                ->leftJoin('desa', 'masyarakat.id_desa', '=', 'desa.id_desa')
                ->leftJoin('kecamatan', 'desa.id_kecamatan', '=', 'kecamatan.id_kecamatan')
                ->select(
                    'masyarakat.id_masyarakat as id',
                    'masyarakat.nama',
                    'masyarakat.email',
                    DB::raw("'Masyarakat' as jenis_pengguna"),
                    'masyarakat.no_telepon',       
                    'masyarakat.jenis_kelamin',     
                    'masyarakat.tanggal_lahir',     
                    'masyarakat.foto',              
                    'masyarakat.saldo',             
                    DB::raw('NULL as kode_anggota'),
                    DB::raw('NULL as id_dinas'),
                    DB::raw('NULL as nama_dinas'),
                    'desa.nama_desa',
                    'kecamatan.nama_kecamatan',
                    'masyarakat.created_at',
                    'masyarakat.updated_at'
                );

            // Filter search untuk masyarakat
            if ($search) {
                $masyarakatQuery->where('masyarakat.nama', 'LIKE', "%{$search}%");
            }

            // Filter kecamatan untuk masyarakat
            if ($kecamatanId) {
                $masyarakatQuery->where('desa.id_kecamatan', $kecamatanId);
            }

            // Filter desa untuk masyarakat
            if ($desaId) {
                $masyarakatQuery->where('masyarakat.id_desa', $desaId);
            }

            // Query PNS
            $pnsQuery = DB::table('pns')
                ->leftJoin('dinas', 'pns.id_dinas', '=', 'dinas.id_dinas')
                ->select(
                    'pns.id_pns as id',
                    'pns.nama',
                    'pns.email',
                    DB::raw("'PNS' as jenis_pengguna"),
                    'pns.no_telepon',
                    'pns.jenis_kelamin',
                    'pns.tanggal_lahir',
                    'pns.foto',
                    'pns.saldo',
                    'pns.kode_anggota',
                    'pns.id_dinas',
                    'dinas.nama_dinas',
                    DB::raw('NULL as nama_desa'),
                    DB::raw('NULL as nama_kecamatan'),
                    'pns.created_at',
                    'pns.updated_at'
                );

            // Filter search untuk pns
            if ($search) {
                $pnsQuery->where('pns.nama', 'LIKE', "%{$search}%");
            }

            // Return hasil union
            return $masyarakatQuery->union($pnsQuery)
                ->orderBy('created_at', 'desc');
        }
    }

    public function export(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $filename = 'data_pengguna_' . ($filter === 'all' ? 'semua' : $filter) . '_' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new DataPenggunaExport($filter), $filename);
    }

    // API untuk detail user
    public function show($type, $id)
    {
        try {
            if ($type === 'masyarakat') {
                $user = \App\Models\Masyarakat::with('desa.kecamatan')->find($id);

                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }

                return response()->json([
                    'id' => $user->id_masyarakat,
                    'type' => 'masyarakat',
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'jenis_kelamin' => $user->jenis_kelamin,     
                    'no_telepon' => $user->no_telepon,           
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'alamat' => $user->alamat,     
                    'saldo' => $user->saldo,                     
                    'nama_dinas' => 'Masyarakat',                
                    'kode_anggota' => $user->barcode_id,
                    'nama_desa' => $user->desa ? $user->desa->nama_desa : null,
                    'nama_kecamatan' => $user->desa && $user->desa->kecamatan ? $user->desa->kecamatan->nama_kecamatan : null,
                    'created_at' => $user->created_at,           
                ]);
            } elseif ($type === 'pns') {
                $user = \App\Models\Pns::with('dinas')->find($id);

                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }

                return response()->json([
                    'id' => $user->id_pns,
                    'type' => 'pns',
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'jenis_kelamin' => $user->jenis_kelamin,
                    'no_telepon' => $user->no_telepon,
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'alamat' => $user->alamat,     
                    'saldo' => $user->saldo,
                    'nama_dinas' => $user->dinas ? $user->dinas->nama_dinas : 'ASN/PNS',
                    'kode_anggota' => $user->kode_anggota,
                    'barcode_id' => $user->barcode_id,
                    'nama_desa' => null,
                    'nama_kecamatan' => null,        
                    'created_at' => $user->created_at,            
                ]);
            }

            return response()->json(['error' => 'Invalid type'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // API untuk mendapatkan daftar desa berdasarkan kecamatan
    public function getDesaByKecamatan($kecamatanId)
    {
        $desas = Desa::where('id_kecamatan', $kecamatanId)
            ->select('id_desa', 'nama_desa')
            ->orderBy('nama_desa')
            ->get();
            
        return response()->json($desas);
    }
}