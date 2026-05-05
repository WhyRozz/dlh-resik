<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penarikan extends Model
{
    protected $table = 'penarikan';
    protected $primaryKey = 'id_penarikan';

    public $timestamps = false;
    
    protected $fillable = [
        'id_masyarakat',
        'id_pns',
        'updated_by',           // ✅ BARU
        'jumlah_uang',
        'jenis_ewallet',
        'nomor_ewallet',
        'status',
        'tanggal_penarikan',
        'tanggal_disetujui',    // ✅ BARU
    ];

    protected $casts = [
        'jumlah_uang' => 'decimal:2',
        'tanggal_penarikan' => 'datetime',
        'tanggal_disetujui' => 'datetime',
    ];

    // Relasi ke Masyarakat
    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat', 'id_masyarakat');
    }

    // Relasi ke PNS
    public function pns()
    {
        return $this->belongsTo(Pns::class, 'id_pns', 'id_pns');
    }
}
