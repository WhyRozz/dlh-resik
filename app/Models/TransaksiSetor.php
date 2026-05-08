<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ✅ FIX: Tambahkan use statements untuk class yang dipanggil
use App\Models\Masyarakat;
use App\Models\Pns;

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

    // ✅ BOOT METHOD: Update saldo otomatis
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaksi) {
            // ✅ Sekarang tidak merah karena sudah di-use di atas
            if ($transaksi->id_masyarakat) {
                Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                    ->increment('saldo', $transaksi->total_rupiah);
            }

            if ($transaksi->id_pns) {
                Pns::where('id_pns', $transaksi->id_pns)
                    ->increment('saldo', $transaksi->total_rupiah);
            }
        });
    }
}