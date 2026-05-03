<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Masyarakat; // ← Import model yang BENAR
use App\Models\Penarikan;  // ← Import model Penarikan
use Carbon\Carbon;

class PenarikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Ambil user dari tabel 'masyarakat'
        $masyarakat = Masyarakat::first();
        
        // Jika tidak ada data masyarakat, buat dummy dulu atau skip
        if (!$masyarakat) {
            $this->command->warn('⚠️ Tidak ada data di tabel masyarakat. Seeder dilewati.');
            return;
        }

        // ✅ Insert data penarikan
        DB::table('penarikans')->insert([
            [
                'user_id' => $masyarakat->id_masyarakat, // ← Gunakan primary key yang benar
                'nama' => $masyarakat->nama,
                'waktu_penarikan' => Carbon::now(),
                'jenis' => 'Dana',
                'nomor_ewallet' => '081234567890',
                'jumlah' => 50000.00,
                'status' => 'Diproses',
                'catatan' => 'Penarikan pertama',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => $masyarakat->id_masyarakat,
                'nama' => $masyarakat->nama,
                'waktu_penarikan' => Carbon::now(),
                'jenis' => 'Tunai',
                'nomor_ewallet' => null,
                'jumlah' => 100000.00,
                'status' => 'Diterima',
                'catatan' => 'Penarikan kedua',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
        
        $this->command->info('✅ PenarikanSeeder berhasil dijalankan!');
    }
}