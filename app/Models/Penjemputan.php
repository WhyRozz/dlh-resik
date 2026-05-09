<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Penjemputan extends Model
{
    protected $table = 'penjemputans';
    protected $fillable = ['foto', 'nama_admin', 'waktu', 'berat', 'lokasi', 'keterangan', 'status'];
    protected $casts = ['berat' => 'decimal:2', 'waktu' => 'datetime'];
}
