<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pns extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pns';
    protected $primaryKey = 'id_pns';
    public $incrementing = true;
    protected $keyType = 'int';

    // ✅ LENGKAP: Semua field yang bisa di-mass-assign
    protected $fillable = [
        'kode_anggota',
        'nama',
        'email',
        'password',
        'no_telepon',
        'tanggal_lahir',
        'jenis_kelamin',
        'foto',
        'alamat',
        'id_dinas',
        'barcode_id',
        'saldo',
        'otp',
        'otp_expires',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'otp_expires' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ Relasi ke Dinas (Many-to-One)
    public function dinas()
    {
        return $this->belongsTo(Dinas::class, 'id_dinas', 'id_dinas');
    }

    // ✅ Relasi ke Laporan (One-to-Many)
    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'id_pns', 'id_pns');
    }

    // ✅ Relasi ke Transaksi
    public function transaksiSetor()
    {
        return $this->hasMany(TransaksiSetor::class, 'id_pns', 'id_pns');
    }

    public function penarikan()
    {
        return $this->hasMany(Penarikan::class, 'id_pns', 'id_pns');
    }
}
