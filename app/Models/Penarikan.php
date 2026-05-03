<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penarikan extends Model
{
    use HasFactory;

    protected $table = 'penarikan';
    protected $primaryKey = 'id_penarikan';
    public $timestamps = false;
    
    protected $fillable = [
        'id_masyarakat',
        'id_pns',
        'jumlah_uang',
        'jenis_ewallet',
        'nomor_ewallet',
        'status',
        'tanggal_penarikan',
    ];
    
    protected $casts = [
        'jumlah_uang' => 'decimal:2',
        'tanggal_penarikan' => 'datetime',
    ];
} 