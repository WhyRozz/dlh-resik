<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'id_kecamatan';
    protected $fillable = ['kode_kecamatan', 'nama_kecamatan'];

    public function desas(): HasMany
    {
        return $this->hasMany(Desa::class, 'id_kecamatan', 'id_kecamatan');
    }
}