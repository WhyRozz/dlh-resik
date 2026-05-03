<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_masyarakat', 'nama', 'lokasi', 'keterangan',
        'status', 'balasan', 'foto', 'created_at', 'tanggal'
    ];

    protected $casts = ['created_at' => 'datetime', 'tanggal' => 'date'];

    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat', 'id_masyarakat');
    }

    public function getFotoUrlAttribute()
    {
        return $this->foto ? asset('storage/uploads/' . $this->foto) : null;
    }
}
