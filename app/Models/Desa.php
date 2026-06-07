<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends Model
{
    protected $table = 'desa';
    protected $primaryKey = 'id_desa';
    protected $fillable = ['id_kecamatan', 'kode_desa', 'nama_desa', 'jenis'];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }

    public function masyarakat(): HasMany
    {
        return $this->hasMany(Masyarakat::class, 'id_desa', 'id_desa');
    }
}