<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjemputan extends Model
{
    protected $table = 'penjemputans'; 
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    public $timestamps = true; // Atau false jika tidak pakai created_at/updated_at

    protected $fillable = [
        'id_petugas',
        'nama_admin',
        'waktu',
        'lokasi',
        'berat',
        'keterangan',
        'status',
        'foto',
    ];

    protected $casts = [
        'berat' => 'decimal:2',
        'waktu' => 'datetime',
    ];

    // Relasi ke Petugas
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }

    // Relasi untuk mendapatkan info wilayah kerja
    public function getWilayahKerjaAttribute()
    {
        if (!$this->petugas) return 'Petugas DLH';

        if ($this->petugas->level === 'petugas_dlh') {
            return 'Petugas DLH';
        } elseif (strpos($this->petugas->level, 'bank_sampah_') === 0) {
            $idDesa = str_replace('bank_sampah_', '', $this->petugas->level);
            $desa = \App\Models\Desa::with('kecamatan')->find($idDesa);
            if ($desa && $desa->kecamatan) {
                return 'Bank Sampah ' . strtoupper($desa->nama_desa) .
                    ' (' . $desa->nama_desa . ', ' . $desa->kecamatan->nama_kecamatan . ')';
            }
        }

        return 'Petugas DLH';
    }

    // Helper untuk mendapatkan ID kecamatan
    public function getIdKecamatanAttribute()
    {
        if (!$this->petugas) return null;

        if (strpos($this->petugas->level, 'bank_sampah_') === 0) {
            $idDesa = str_replace('bank_sampah_', '', $this->petugas->level);
            $desa = \App\Models\Desa::find($idDesa);
            return $desa?->id_kecamatan;
        }

        return null;
    }
}
