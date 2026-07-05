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
        'fcm_token',
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

    // Helper untuk generate level value dari desa
    public static function generateLevelValue($desa)
    {
        // ✅ Format BARU: bank_sampah_{id_desa}
        return 'bank_sampah_' . $desa->id_desa;
    }

    // Helper untuk get nama level yang readable
    public function getLevelNameAttribute()
    {
        if ($this->level === 'petugas_dlh') {
            return 'Petugas DLH';
        }

        // ✅ Format BARU: bank_sampah_{id_desa}
        if (strpos($this->level, 'bank_sampah_') === 0) {
            $idDesa = (int) str_replace('bank_sampah_', '', $this->level);
            $desa = \App\Models\Desa::with('kecamatan')->find($idDesa);

            if ($desa && $desa->kecamatan) {
                return 'Bank Sampah ' . strtoupper($desa->nama_desa) .
                    ' (' . $desa->nama_desa . ', ' . $desa->kecamatan->nama_kecamatan . ')';
            }
        }

        return ucfirst(str_replace('_', ' ', $this->level));
    }

    public function getDesaIdAttribute()
    {
        if (strpos($this->level, 'bank_sampah_') === 0) {
            return (int) str_replace('bank_sampah_', '', $this->level);
        }
        return null;
    }

    public function getDesaAttribute()
    {
        $idDesa = $this->desa_id;
        if ($idDesa) {
            return \App\Models\Desa::with('kecamatan')->find($idDesa);
        }
        return null;
    }

    // Helper untuk get nama wilayah dari level
    public function getNamaWilayahAttribute()
    {
        if ($this->level === 'petugas_dlh') {
            return 'Petugas DLH';
        }

        if (strpos($this->level, 'bank_sampah_') === 0) {
            $idDesa = (int) str_replace('bank_sampah_', '', $this->level);
            $desa = \App\Models\Desa::with('kecamatan')->find($idDesa);

            if ($desa && $desa->kecamatan) {
                return 'Bank Sampah Kecamatan ' .
                    $desa->kecamatan->nama_kecamatan .
                    ', Desa ' .
                    $desa->nama_desa;
            }
        }

        return ucfirst(str_replace('_', ' ', $this->level));
    }
}
