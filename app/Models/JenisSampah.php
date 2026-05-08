<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    protected $table = 'jenis_sampah';
    protected $primaryKey = 'id_jenis_sampah';
    public $timestamps = false; // Karena di screenshot tidak ada created_at/updated_at

    protected $fillable = [
        'jenis',
        'satuan',
        'harga',
        'gambar',
    ];

    // ✅ Otomatis convert 'harga' jadi angka desimal saat dibaca
    protected $casts = [
        'harga' => 'decimal:2',
    ];
}
