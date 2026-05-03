<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dinas extends Model
{
    use HasFactory;

    protected $table = 'dinas';
    protected $primaryKey = 'id_dinas';

    protected $fillable = [
        'nama_dinas',
    ];

    // Relasi ke PNS
    public function pns()
    {
        return $this->hasMany(Pns::class, 'id_dinas', 'id_dinas');
    }
}
