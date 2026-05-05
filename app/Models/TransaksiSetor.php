<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiSetor extends Model
{
    use HasFactory;

    protected $table = 'transaksi_setor';
    protected $primaryKey = 'id_transaksi';
    public $incrementing = true;
    public $timestamps = false; // ✅ Sesuai DB: tidak ada created_at/updated_at

    protected $fillable = [
        'id_masyarakat',
        'id_pns',
        'id_jenis_sampah',
        'berat',
        'harga_per_kg',
        'total_rupiah',
        'id_petugas',
        'tanggal_transaksi',
    ];

    protected $casts = [
        'berat' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'total_rupiah' => 'decimal:2',
        'tanggal_transaksi' => 'datetime',
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

    // ✅ ACCESSOR: Ambil nama pengsetor (Masyarakat atau PNS)
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

    // ✅ ACCESSOR: Tipe pengsetor
    public function getTipePengsetorAttribute()
    {
        if ($this->id_masyarakat) return 'Masyarakat';
        if ($this->id_pns) return 'PNS';
        return '-';
    }

    // ✅ BOOT METHOD: Update saldo otomatis saat transaksi dibuat
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaksi) {
            // Update saldo masyarakat
            if ($transaksi->id_masyarakat) {
                Masyarakat::where('id_masyarakat', $transaksi->id_masyarakat)
                    ->increment('saldo', $transaksi->total_rupiah);
            }

            // Update saldo PNS
            if ($transaksi->id_pns) {
                Pns::where('id_pns', $transaksi->id_pns)
                    ->increment('saldo', $transaksi->total_rupiah);
            }
        });
    }
}