// app/Http/Controllers/Api/PenarikanController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Masyarakat;
use App\Models\Pns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenarikanController extends Controller
{
// ✅ Submit Penarikan Baru
public function store(Request $request)
{
$validator = Validator::make($request->all(), [
'user_id' => 'required|integer',
'tipe_user' => 'required|in:masyarakat,pns',
'nama' => 'required|string|max:255',
'e_wallet' => 'required|in:Dana,OVO,GoPay,ShopeePay',
'nomor_e_wallet' => 'required|string|min:10|max:20',
'nominal' => 'required|integer|min:50000',
]);

if ($validator->fails()) {
return response()->json([
'status' => 'error',
'message' => $validator->errors()->first()
], 422);
}

try {
DB::beginTransaction();

// 1. Cek user & saldo
$user = $request->tipe_user == 'masyarakat'
? Masyarakat::find($request->user_id)
: Pns::find($request->user_id);

if (!$user) {
return response()->json([
'status' => 'error',
'message' => 'User tidak ditemukan'
], 404);
}

// 2. Cek saldo cukup
if ($user->saldo < $request->nominal) {
    return response()->json([
    'status' => 'error',
    'message' => 'Saldo tidak mencukupi'
    ], 400);
    }

    // 3. Potong saldo user
    $user->saldo -= $request->nominal;
    $user->save();

    // 4. Simpan penarikan
    $penarikan = Penarikan::create([
    'user_id' => $request->user_id,
    'tipe_user' => $request->tipe_user,
    'nama' => $request->nama,
    'e_wallet' => $request->e_wallet,
    'nomor_e_wallet' => $request->nomor_e_wallet,
    'nominal' => $request->nominal,
    'status' => 'pending',
    ]);

    DB::commit();

    return response()->json([
    'status' => 'success',
    'message' => 'Pengajuan penarikan berhasil',
    'data' => [
    'id' => $penarikan->id,
    'nominal' => $penarikan->nominal,
    'status' => $penarikan->status,
    ]
    ], 200);

    } catch (\Exception $e) {
    DB::rollBack();
    return response()->json([
    'status' => 'error',
    'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
    ], 500);
    }
    }

    // ✅ Get Riwayat Penarikan User
    public function index(Request $request)
    {
    $validator = Validator::make($request->all(), [
    'user_id' => 'required|integer',
    'tipe_user' => 'required|in:masyarakat,pns',
    ]);

    if ($validator->fails()) {
    return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
    }

    $penarikans = Penarikan::where('user_id', $request->user_id)
    ->where('tipe_user', $request->tipe_user)
    ->orderBy('created_at', 'desc')
    ->get()
    ->map(function($p) {
    return [
    'id' => $p->id,
    'tanggal' => $p->created_at->format('l, d F Y'),
    'nominal' => 'Rp ' . number_format($p->nominal, 0, ',', '.'),
    'metode' => $p->e_wallet,
    'status' => ucfirst($p->status),
    'id_transaksi' => $p->id_transaksi,
    ];
    });

    return response()->json([
    'status' => 'success',
    'data' => $penarikans
    ]);
    }
    }
