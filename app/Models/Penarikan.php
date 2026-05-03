<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penarikan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'waktu_penarikan',
        'jenis',
        'nomor_ewallet',
        'jumlah',
        'status',
        'catatan',
    ];

    protected $casts = [
        'waktu_penarikan' => 'datetime',
        'jumlah' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}