<?php
namespace App\Http\Controllers\BankSampah;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PenjemputanController extends Controller
{
    protected $table = 'penjemputans';

    public function index()
    {
        // Hanya tampilkan yang status 'diproses' (opsional: bisa hapus where jika ingin lihat semua)
        $penjemputans = DB::table($this->table)
            ->orderBy('waktu', 'desc')
            ->get();
            
        return view('admin.bank-sampah.penjemputan.index', compact('penjemputans'));
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
            ->update(['status' => 'berhasil']);

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