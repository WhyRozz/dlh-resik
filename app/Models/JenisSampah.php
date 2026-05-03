<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    use HasFactory;

    protected $table = 'jenis_sampah';

    // Set primary key
    protected $primaryKey = 'id_jenis_sampah';

    // Disable timestamps jika tidak ada created_at dan updated_at
    public $timestamps = false;

    protected $fillable = [
        'gambar',
        'jenis',
        'satuan',
        'harga',
    ];

    protected $casts = [
        'harga' => 'integer',
    ];
}
