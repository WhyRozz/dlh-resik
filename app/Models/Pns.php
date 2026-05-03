<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pns extends Model
{
    use HasFactory;

    protected $table = 'pns';
    protected $primaryKey = 'id_pns';

    protected $fillable = [
        'kode_anggota',
        'nama',
        'email',
        'password',
        'no_telepon',
        'tanggal_lahir',
        'jenis_kelamin',
        'foto',
        'saldo',
        'id_dinas',
        'otp',
        'otp_expires',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'tanggal_lahir' => 'date',
    ];

    // Relasi ke tabel dinas
    public function dinas()
    {
        return $this->belongsTo(Dinas::class, 'id_dinas', 'id_dinas');
    }
}
