<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PenjemputanController extends Controller
{
    protected $table = 'penjemputans';

    public function index(Request $request)  // ✅ Tambah parameter $request
    {
        $query = DB::table($this->table);

        // ✅ Filter Bulan
        if ($request->filled('bulan')) {
            $query->whereMonth('waktu', $request->bulan);
        }

        // ✅ Filter Tahun
        if ($request->filled('tahun')) {
            $query->whereYear('waktu', $request->tahun);
        }

        // ✅ Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ✅ Pagination & Ordering
        $penjemputans = $query->orderBy('waktu', 'desc')->paginate(15);

        // ✅ Range Tahun Otomatis (5 tahun lalu - 5 tahun depan)
        $tahunList = collect(range(date('Y') - 5, date('Y') + 5));

        return view('admin.bank-sampah.penjemputan.index', compact('penjemputans', 'tahunList'));
    }

    public function show($id)
    {
        $item = DB::table($this->table)->where('id', $id)->first();
        if (!$item) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
        return response()->json($item);
    }

    public function approve($id)
    {
        $affected = DB::table($this->table)
            ->where('id', $id)
            ->where('status', 'diproses') // Cegah approve ulang
            ->update(['status' => 'disetujui']);

        if (!$affected) {
            return redirect()->back()->with('error', 'Data sudah diproses atau tidak ditemukan.');
        }
        return redirect()->back()->with('success', 'Penjemputan berhasil disetujui.');
    }

    public function reject($id)
    {
        $affected = DB::table($this->table)
            ->where('id', $id)
            ->where('status', 'diproses')
            ->update(['status' => 'ditolak']);

        if (!$affected) {
            return redirect()->back()->with('error', 'Gagal menolak data.');
        }
        return redirect()->back()->with('success', 'Penjemputan ditolak.');
    }
}
