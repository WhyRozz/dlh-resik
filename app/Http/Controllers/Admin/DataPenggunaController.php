<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Masyarakat;
use App\Models\Pns;
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
        
        $query = $this->getUsersQuery($filter, $search);
        
        $users = $query->paginate(15);

        return view('admin.data-pengguna.index', compact('users', 'filter', 'search'));
    }

    private function getUsersQuery($filter, $search = '')
    {
        if ($filter === 'masyarakat') {
            // ✅ Simpan ke variabel $query dulu
            $query = DB::table('masyarakat')
                ->select(
                    'masyarakat.id_masyarakat as id',
                    'masyarakat.nama',
                    'masyarakat.email',
                    DB::raw("'Masyarakat' as jenis_pengguna"),
                    DB::raw('NULL as no_telepon'),
                    DB::raw('NULL as jenis_kelamin'),
                    DB::raw('NULL as tanggal_lahir'),
                    DB::raw('NULL as foto'),
                    DB::raw('0 as saldo'),
                    DB::raw('NULL as kode_anggota'),
                    DB::raw('NULL as id_dinas'),
                    DB::raw('NULL as nama_dinas'),
                    'masyarakat.created_at',
                    'masyarakat.updated_at'
                );

            // ✅ Tambah filter search DI SINI (setelah select, sebelum return)
            if ($search) {
                $query->where('masyarakat.nama', 'LIKE', "%{$search}%");
            }

            // ✅ Return query yang sudah lengkap
            return $query->orderBy('masyarakat.created_at', 'desc');

        } elseif ($filter === 'asn') {
            // ✅ Simpan ke variabel $query dulu
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
                    'pns.created_at',
                    'pns.updated_at'
                );

            // ✅ Tambah filter search DI SINI
            if ($search) {
                $query->where('pns.nama', 'LIKE', "%{$search}%");
            }

            // ✅ Return query yang sudah lengkap
            return $query->orderBy('pns.created_at', 'desc');

        } else {
            // UNION untuk semua
            
            //  Query Masyarakat
            $masyarakatQuery = DB::table('masyarakat')
                ->select(
                    'masyarakat.id_masyarakat as id',
                    'masyarakat.nama',
                    'masyarakat.email',
                    DB::raw("'Masyarakat' as jenis_pengguna"),
                    DB::raw('NULL as no_telepon'),
                    DB::raw('NULL as jenis_kelamin'),
                    DB::raw('NULL as tanggal_lahir'),
                    DB::raw('NULL as foto'),
                    DB::raw('0 as saldo'),
                    DB::raw('NULL as kode_anggota'),
                    DB::raw('NULL as id_dinas'),
                    DB::raw('NULL as nama_dinas'),
                    'masyarakat.created_at',
                    'masyarakat.updated_at'
                );

            // ✅ Tambah filter search untuk masyarakat
            if ($search) {
                $masyarakatQuery->where('masyarakat.nama', 'LIKE', "%{$search}%");
            }

            // ✅ Query PNS
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
                    'pns.created_at',
                    'pns.updated_at'
                );

            // ✅ Tambah filter search untuk pns
            if ($search) {
                $pnsQuery->where('pns.nama', 'LIKE', "%{$search}%");
            }

            // ✅ Return hasil union
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
                $user = \App\Models\Masyarakat::find($id);

                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }

                return response()->json([
                    'id' => $user->id_masyarakat,
                    'type' => 'masyarakat',
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'jenis_kelamin' => null,
                    'no_telepon' => null,
                    'saldo' => 0,
                    'nama_dinas' => null,
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
                    'saldo' => $user->saldo,
                    'nama_dinas' => $user->dinas ? $user->dinas->nama_dinas : null,
                ]);
            }

            return response()->json(['error' => 'Invalid type'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}