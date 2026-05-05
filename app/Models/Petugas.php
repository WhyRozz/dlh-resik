<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Petugas extends Authenticatable
{
    use HasFactory;

    protected $table = 'petugas';
    protected $primaryKey = 'id_petugas';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'password',
        'no_telepon',
        'foto',
        'level',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke transaksi setor
    public function transaksiSetor()
    {
        return $this->hasMany(TransaksiSetor::class, 'id_petugas', 'id_petugas');
    }
    // Helper untuk ambil nama lengkap
    public function getNamaLengkapAttribute($value)
    {
        return $value;
    }
}