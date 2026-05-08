<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tps extends Model
{
    protected $table = 'tps';

    // ✅ PENTING: Set primary key yang benar
    protected $primaryKey = 'id_tps';

    public $timestamps = false; // Kalau tabel tidak punya created_at/updated_at

    protected $fillable = [
        'nama_tps',
        'lokasi',
        'alamat',
        'kapasitas',
        'keterangan',
    ];
}
