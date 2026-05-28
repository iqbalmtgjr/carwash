<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Rotate QR token absensi setiap 1 menit
        $schedule->command('qr:generate')->everyMinute();

        // Tutup absensi setiap hari jam 17:00, tandai yang tidak hadir
        $schedule->command('attendance:close')->dailyAt('17:00');

        // Generate payroll otomatis setiap Minggu jam 17:30
        $schedule->command('payroll:generate')->weeklyOn(Carbon::SUNDAY, '17:30');

        // Cek absensi setiap hari jam 08:15, notifikasi owner jika kosong
        $schedule->command('notifikasi:owner')->dailyAt('08:15');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
