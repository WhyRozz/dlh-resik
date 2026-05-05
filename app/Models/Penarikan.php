// app/Models/Penarikan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penarikan extends Model
{
    protected $table = 'penarikans';

    protected $fillable = [
        'user_id',
        'tipe_user',
        'nama',
        'e_wallet',
        'nomor_e_wallet',
        'nominal',
        'status',
        'id_transaksi',
        'catatan_admin',
        'diproses_at',
        'selesai_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'diproses_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    // Scope untuk filter status
    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeSelesai($query) {
        return $query->where('status', 'selesai');
    }
}
