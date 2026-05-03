<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Petugas extends Model
{
    use SoftDeletes;
    
    protected $table = 'petugas';
    protected $primaryKey = 'id_petugas';
    
    protected $fillable = [
        'nama_lengkap',
        'email',
        'password',
        'password_encrypted',
        'no_telepon',
        'level',
        'is_active',
    ];
    
    protected $hidden = [
        'password',
        'password_encrypted',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}