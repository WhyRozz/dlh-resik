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

    // Helper untuk generate level value dari desa
    public static function generateLevelValue($desa)
    {
        $kecamatan = $desa->kecamatan->nama_kecamatan ?? '';
        $desaName = $desa->nama_desa;

        // Format: bank_sampah_{desa}_{kecamatan}
        $slugDesa = strtolower(str_replace(' ', '_', $desaName));
        $slugKecamatan = strtolower(str_replace(' ', '_', $kecamatan));

        return 'bank_sampah_' . $slugDesa . '_' . $slugKecamatan;
    }

    // Helper untuk get nama level yang readable
    public function getLevelNameAttribute()
    {
        if ($this->level === 'petugas_dlh') {
            return 'Petugas DLH';
        }

        // Format: Bank Sampah {DESA} ({KECAMATAN})
        $parts = explode('_', str_replace('bank_sampah_', '', $this->level));
        if (count($parts) >= 2) {
            $kecamatan = ucfirst(str_replace('_', ' ', end($parts)));
            $desa = ucfirst(str_replace('_', ' ', implode(' ', array_slice($parts, 0, -1))));
            return 'Bank Sampah ' . strtoupper($desa) . ' (' . $desa . ', ' . $kecamatan . ')';
        }

        return ucfirst(str_replace('_', ' ', $this->level));
    }

    // Relasi ke desa (extract dari level)
    public function desa()
    {
        // Extract id_desa dari level (format: bank_sampah_{id_desa})
        if (strpos($this->level, 'bank_sampah_') === 0) {
            $idDesa = str_replace('bank_sampah_', '', $this->level);
            return $this->belongsTo(Desa::class, 'level', 'id_desa')
                ->where('level', 'LIKE', 'bank_sampah_%');
        }
        return null;
    }

    // Helper untuk get nama desa dari level
    public function getNamaWilayahAttribute()
    {
        if ($this->level === 'petugas_dlh') {
            return 'Petugas DLH';
        }

        if (strpos($this->level, 'bank_sampah_') === 0) {
            $idDesa = str_replace('bank_sampah_', '', $this->level);
            $desa = Desa::with('kecamatan')->find($idDesa);

            if ($desa) {
                return 'Bank Sampah ' . strtoupper($desa->nama_desa) .
                    ' (' . $desa->nama_desa . ', ' . $desa->kecamatan->nama_kecamatan . ')';
            }
        }

        return ucfirst(str_replace('_', ' ', $this->level));
    }
}
