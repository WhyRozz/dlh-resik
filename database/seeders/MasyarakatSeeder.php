<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MasyarakatSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('masyarakat')->insert([
            [
                'nama' => 'User Demo',
                'email' => 'user@example.com',
                'password' => Hash::make('password123'), // ✅ Password di-hash!
                'otp' => null,
                'otp_expires' => null,
                'google_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Admin Demo',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'otp' => null,
                'otp_expires' => null,
                'google_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
        
        $this->command->info('✅ MasyarakatSeeder berhasil!');
    }
}