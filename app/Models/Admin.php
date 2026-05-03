<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'password',
        'password_encrypted',
        'otp',
        'otp_expires',
    ];

    protected $hidden = [
        'password',
        'password_encrypted',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'otp_expires' => 'datetime',
    ];

    /**
     * Cek apakah ini akun default
     */
    public function isDefault(): bool
    {
        return $this->email === 'simpelsi2025@gmail.com';
    }
}
