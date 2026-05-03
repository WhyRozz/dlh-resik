<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Masyarakat extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'masyarakat';
    protected $primaryKey = 'id_masyarakat';
    public $incrementing = true;
    protected $keyType = 'int';

    // ✅ LENGKAP: Semua field yang bisa di-mass-assign
    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_telepon',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat',
        'foto',
        'barcode_id',
        'saldo',
        'otp',
        'otp_expires',
    ];

    protected $hidden = [
        'password',
        'remember_token', // Hapus ini kalau kolom tidak ada di DB
    ];

    protected $casts = [
        'otp_expires' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke Laporan
    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'id_masyarakat', 'id_masyarakat');
    }

    // ✅ Tambahan: Relasi ke Transaksi
    public function transaksiSetor()
    {
        return $this->hasMany(TransaksiSetor::class, 'id_masyarakat', 'id_masyarakat');
    }

    public function penarikan()
    {
        return $this->hasMany(Penarikan::class, 'id_masyarakat', 'id_masyarakat');
    }
}
