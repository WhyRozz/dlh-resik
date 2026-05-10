<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ✅ FIX: Tambahkan use statements untuk class yang dipanggil
use App\Models\Masyarakat;
use App\Models\Pns;
use App\Models\JenisSampah;
use App\Models\Petugas;
use Illuminate\Http\Request;

class TransaksiSetor extends Model
{
    use HasFactory;

    protected $table = 'transaksi_setor';
    protected $primaryKey = 'id_transaksi';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_masyarakat',
        'id_pns',
        'id_jenis_sampah',
        'berat',
        'harga_per_kg',
        'total_rupiah',
        'id_petugas',
        'tanggal_transaksi',
        'berat_asli',
        'status_transaksi',
        'catatan_koreksi',
        'dikoreksi_oleh',
        'tanggal_koreksi',
    ];

    protected $casts = [
        'berat' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'total_rupiah' => 'decimal:2',
        'tanggal_transaksi' => 'datetime',
        'tanggal_koreksi' => 'datetime',
    ];

    // ✅ RELASI
    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat', 'id_masyarakat');
    }

    public function pns()
    {
        return $this->belongsTo(Pns::class, 'id_pns', 'id_pns');
    }

    public function jenisSampah()
    {
        return $this->belongsTo(JenisSampah::class, 'id_jenis_sampah', 'id_jenis_sampah');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }

    // ✅ ACCESSOR
    public function getNamaPengsetorAttribute()
    {
        if ($this->masyarakat) {
            return $this->masyarakat->nama;
        }
        if ($this->pns) {
            return $this->pns->nama;
        }
        return '-';
    }

    public function getTipePengsetorAttribute()
    {
        if ($this->id_masyarakat) return 'Masyarakat';
        if ($this->id_pns) return 'PNS';
        return '-';
    }
    /**
     * GET /api/riwayat-setor
     * Ambil riwayat setor user
     */
    public function riwayatSetor(Request $request)
    {
        $idMasyarakat = $request->query('id_masyarakat');
        $idPns = $request->query('id_pns');
        $tipeUser = $request->query('tipe_user');

        if (!$tipeUser || (!$idMasyarakat && !$idPns)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter user tidak lengkap'
            ], 422);
        }

        try {
            // ✅ QUERY TANPA FILTER STATUS (karena kolom tidak ada di DB)
            $query = TransaksiSetor::with(['jenisSampah', 'petugas'])
                ->orderBy('tanggal_transaksi', 'desc');

            if ($tipeUser === 'masyarakat' && $idMasyarakat) {
                $query->where('id_masyarakat', $idMasyarakat);
            } elseif ($tipeUser === 'pns' && $idPns) {
                $query->where('id_pns', $idPns);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tipe user atau ID tidak valid'
                ], 422);
            }

            $riwayat = $query->get()->map(function ($trx) {
                return [
                    'id_transaksi' => $trx->id_transaksi,
                    'tanggal_transaksi' => $trx->tanggal_transaksi,
                    'jenis_sampah' => $trx->jenisSampah?->jenis ?? 'Umum',
                    'berat' => $trx->berat,
                    'harga_per_kg' => $trx->harga_per_kg ?? $trx->jenisSampah?->harga ?? 0,
                    'total_rupiah' => $trx->total_rupiah ?? (($trx->jenisSampah?->harga ?? 0) * $trx->berat),
                    'nama_petugas' => $trx->petugas?->nama_lengkap ?? $trx->petugas?->nama ?? '-',
                    'status' => 'selesai', // ✅ Hardcode karena kolom tidak ada
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $riwayat,
                'total' => $riwayat->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Riwayat Setor Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
