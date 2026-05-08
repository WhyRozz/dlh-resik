<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiSetor extends Model
{
    protected $table = 'transaksi_setor';
    protected $primaryKey = 'id_transaksi';
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

    // Relasi
    public function masyarakat() {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat');
    }

    public function pns() {
        return $this->belongsTo(Pns::class, 'id_pns');
    }

    public function jenisSampah() {
        return $this->belongsTo(JenisSampah::class, 'id_jenis_sampah');
    }

    public function petugas() {
        return $this->belongsTo(Petugas::class, 'id_petugas');
    }
}
