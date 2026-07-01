<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'email',
        'no_telepon',
        'password',
        'password_encrypted',
        'otp',
        'otp_expires',
        'fcm_token',
        'role',
        'id_desa',
        'id_kecamatan',
    ];

    protected $hidden = [
        'password',
        'password_encrypted',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'otp_expires' => 'datetime',
    ];

    /**
     * Cek apakah ini akun default
     */
    public function isDefault(): bool
    {
        return $this->email === 'simpelsi2025@gmail.com';
    }

    // ✅ Cek role
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isSubAdminDesa(): bool
    {
        return $this->role === 'sub_admin_desa';
    }

    // ✅ Relasi ke Desa
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    // ✅ Relasi ke Kecamatan
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }

    // ✅ Get nama wilayah lengkap
    public function getNamaWilayahAttribute()
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }

        if ($this->desa && $this->kecamatan) {
            return 'Desa ' . $this->desa->nama_desa . ', Kec. ' . $this->kecamatan->nama_kecamatan;
        }

        return '-';
    }
}