<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateMasyarakatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // HANYA JIKA TABEL MASIH KOSONG
        if (\App\Models\Masyarakat::count() === 0) {
            // Copy data dari database lama via koneksi terpisah
            $oldDB = DB::connection('mysql_old')->table('masyarakat')->get();

            foreach ($oldDB as $user) {
                \App\Models\Masyarakat::create([
                    'id_masyarakat' => $user->id_masyarakat,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'password' => $user->password, // Sudah hash, aman
                    'google_id' => $user->google_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        }
    }
}
