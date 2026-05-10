<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjemputan extends Model
{
    protected $table = 'penjemputans'; // ✅ Sesuaikan dengan nama tabel kamu
    protected $primaryKey = 'id_penjemputan'; // ✅ Sesuaikan primary key
    public $incrementing = true;
    public $timestamps = true; // Atau false jika tidak pakai created_at/updated_at

    protected $fillable = [
        'id_petugas',
        'nama_admin',
        'waktu',
        'lokasi',
        'berat',
        'keterangan',
        'status',
        'foto',
    ];

    protected $casts = [
        'berat' => 'decimal:2',
        'waktu' => 'datetime',
    ];

    // Relasi ke Petugas
    public function petugas() {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }
}
