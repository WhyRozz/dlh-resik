<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Penjemputan;
use Illuminate\Support\Facades\Auth;

class PenjemputanController extends Controller
{
    protected $table = 'penjemputans';

    // ========================================
    // 🔹 WEB ADMIN METHODS (Untuk Halaman Dashboard)
    // ========================================

    public function index(Request $request)
    {
        $query = DB::table($this->table);

// ✅ FILTER OTOMATIS UNTUK SUB ADMIN DESA
        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();
        if ($admin && $admin->isSubAdminDesa() && $admin->id_desa) {
            // Filter berdasarkan nama_admin yang berisi id_desa
            $query->where('nama_admin', 'LIKE', '%bank_sampah_' . $admin->id_desa . '%');
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('waktu', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('waktu', $request->tahun);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penjemputans = $query->orderBy('waktu', 'desc')->paginate(15);
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
            ->where('status', 'diproses')
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

    // ========================================
    // 🔹 ADMIN AJAX METHODS (Dari folder Admin, Direname)
    // ========================================

    /**
     * Store data penjemputan via AJAX (Admin Web)
     * Endpoint: POST /admin/bank-sampah/penjemputan/store
     */
    public function storeFromAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'nama_admin' => 'required|string',
            'waktu' => 'required|date',
            'berat' => 'required|numeric',
            'lokasi' => 'required|string',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:diproses,disetujui,ditolak',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('penjemputan', 'public');
        }

        $penjemputan = Penjemputan::create($data);

        return response()->json(['status' => 'success', 'message' => 'Penjemputan berhasil diajukan', 'data' => $penjemputan], 201);
    }

    /**
     * Get list by admin ID via AJAX (Admin Web)
     * Endpoint: GET /admin/bank-sampah/penjemputan/list/{adminId}
     */
    public function indexByAdmin($adminId)
    {
        $data = Penjemputan::where('nama_admin', $adminId)
            ->orWhere('id', 'like', "%$adminId%")
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
