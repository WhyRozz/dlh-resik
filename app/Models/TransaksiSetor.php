<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiSetor extends Model
{
    use HasFactory;

    protected $table = 'transaksi_setor';
    protected $primaryKey = 'id_transaksi';
    
    // ⚠️ PENTING: Tabel TIDAK punya created_at & updated_at
    // Jadi kita matikan timestamps
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
    ];

    protected $casts = [
        'berat' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'total_rupiah' => 'decimal:2',
        'tanggal_transaksi' => 'datetime',
    ];

    // Relasi ke tabel masyarakat
    public function masyarakat(): BelongsTo
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat', 'id');
    }

    // Relasi ke tabel pns (jika ada)
    public function pns(): BelongsTo
    {
        return $this->belongsTo(Pns::class, 'id_pns', 'id');
    }

    // Relasi ke tabel jenis_sampah
    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'id_jenis_sampah', 'id');
    }

    // Relasi ke tabel petugas
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id');
    }
}