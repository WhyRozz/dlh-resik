// app/Models/Penarikan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penarikan extends Model
{
<<<<<<< HEAD
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
=======
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
>>>>>>> 4915c6c75eb064aefe4b06734d4c4f1e990f8350
