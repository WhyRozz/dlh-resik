<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Penjemputan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\penjemputan as PenjemputanModel;

class PenjemputanController extends Controller
{
    public function store(Request $request)
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

        if ($validator->fails()) return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('penjemputan', 'public');
        }

        $penjemputan = Penjemputan::create($data);

        return response()->json(['status' => 'success', 'message' => 'Penjemputan berhasil diajukan', 'data' => $penjemputan], 201);
    }

    public function index($adminId)
    {
        $data = Penjemputan::where('nama_admin', $adminId)
            ->orWhere('id', 'like', "%$adminId%")
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
