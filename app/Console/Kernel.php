<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // ℹ️ Scheduler "auto-confirm-setor" sudah DIHAPUS
        //    karena fitur auto-confirm tidak dipakai lagi.
        //    (Konfirmasi setor sekarang dilakukan MANUAL oleh petugas
        //     melalui KonfirmasiSetorController.)
        //
        // Tambahkan scheduler lain di sini jika suatu saat perlu, contoh:
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}