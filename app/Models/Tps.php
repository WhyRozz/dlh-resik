<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tps extends Model
{
    use HasFactory;

    protected $table = 'tps';
    protected $primaryKey = 'id_tps';
    public $timestamps = false;

    protected $fillable = [
        'nama_tps',
        'lokasi',
        'alamat',
        'kapasitas',
        'keterangan'
    ];

    /**
     * Parse koordinat "-7.601478,111.943225" jadi array [lat, lng]
     */
    public function getCoordinatesAttribute()
    {
        if (!$this->lokasi) return null;
        $parts = explode(',', $this->lokasi);
        if (count($parts) === 2) {
            return [
                'latitude' => trim($parts[0]),
                'longitude' => trim($parts[1]),
            ];
        }
        return null;
    }

    /**
     * Generate Google Maps URL
     */
    public function getMapsUrlAttribute()
    {
        return $this->lokasi ? "https://maps.google.com/maps?q=" . urlencode($this->lokasi) : null;
    }
}
