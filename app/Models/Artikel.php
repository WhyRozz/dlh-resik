<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    use HasFactory;

    protected $table = 'artikel';
    protected $primaryKey = 'id_artikel';
    public $timestamps = false;

    protected $fillable = [
        'judul',
        'deskripsi',
        'foto',
        'tanggal'
    ];

    protected $casts = [
        'tanggal' => 'datetime'
    ];

    /**
     * Get foto URL attribute
     */
    public function getFotoUrlAttribute()
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}