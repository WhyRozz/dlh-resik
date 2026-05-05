<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penarikan extends Model
{
    protected $table = 'penarikan';
    protected $primaryKey = 'id_penarikan';

    public $timestamps = false;
    
    protected $fillable = [
        'id_masyarakat',
        'id_pns',
        'updated_by',  // Tetap disimpan, tapi tidak direlasi
        'jumlah_uang',
        'jenis_ewallet',
        'nomor_ewallet',
        'status',
        'tanggal_penarikan',
        'tanggal_disetujui',
    ];

    protected $casts = [
        'jumlah_uang' => 'decimal:2',
        'tanggal_penarikan' => 'datetime',
        'tanggal_disetujui' => 'datetime',
    ];

    public function masyarakat(): BelongsTo
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat');
    }

    public function pns(): BelongsTo
    {
        return $this->belongsTo(Pns::class, 'id_pns');
    }

    // HAPUS relasi admin di bawah ini
    // public function admin(): BelongsTo { ... }

    public function getNamaUserAttribute()
    {
        return $this->masyarakat->nama ?? $this->pns->nama ?? 'Unknown';
    }

    public function getFormattedJumlahAttribute()
    {
        return 'Rp ' . number_format($this->jumlah_uang, 0, ',', '.');
    }
}