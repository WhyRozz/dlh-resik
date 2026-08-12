<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penarikan extends Model
{
    protected $table = 'penarikan';
    protected $primaryKey = 'id_penarikan';

    public $timestamps = false;    // TERUS IKI SEHARUSE FALSE/TRUE?

    // ✅ Gunakan tanggal_penarikan sebagai created_at
    const CREATED_AT = 'tanggal_penarikan';
    const UPDATED_AT = 'tanggal_disetujui';

    protected $fillable = [
        'id_masyarakat',
        'id_pns',
        'nama_penerima', 
        'jumlah_uang',
        'jenis_ewallet',
        'nomor_ewallet',
        'jenis_layanan',
        'nama_bank',
        'status',
        'alasan_penolakan',
        'tanggal_penarikan',
        'updated_by',
        'tanggal_disetujui',
    ];

    protected $casts = [
        'jumlah_uang' => 'decimal:2',
        'tanggal_penarikan' => 'datetime',
        'tanggal_disetujui' => 'datetime',
    ];

    // Relasi ke Masyarakat
    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat', 'id_masyarakat');
    }

    // Relasi ke PNS
    public function pns()
    {
        return $this->belongsTo(Pns::class, 'id_pns', 'id_pns');
    }
}
