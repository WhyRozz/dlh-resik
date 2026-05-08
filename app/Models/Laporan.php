<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';
    protected $primaryKey = 'id';
    public $timestamps = false; // Karena cuma ada created_at

    protected $fillable = [
        'id_masyarakat',
        'id_pns',        
        'nama',
        'lokasi',
        'keterangan',
        'status',
        'balasan',
        'foto',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
    ];

    // ✅ Relasi ke Masyarakat
    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat', 'id_masyarakat');
    }

    // ✅ Relasi ke PNS
    public function pns()
    {
        return $this->belongsTo(Pns::class, 'id_pns', 'id_pns');
    }

    // ✅ Helper untuk dapat pelapor
    public function pelapor()
    {
        if ($this->id_masyarakat) {
            return $this->masyarakat;
        }
        return $this->pns;
    }
}
