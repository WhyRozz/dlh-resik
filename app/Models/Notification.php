<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'tipe_user',
        'title',
        'body',
        'type',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke Masyarakat
    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'user_id', 'id_masyarakat')
            ->where('tipe_user', 'masyarakat');
    }

    // Relasi ke PNS
    public function pns()
    {
        return $this->belongsTo(Pns::class, 'user_id', 'id_pns')
            ->where('tipe_user', 'pns');
    }

    // Relasi ke Petugas
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'user_id', 'id_petugas')
            ->where('tipe_user', 'petugas');
    }
}
