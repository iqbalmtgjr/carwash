<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifikasiOwner extends Command
{
    protected $signature = 'notifikasi:owner';

    protected $description = 'Kirim notifikasi ke owner jika tidak ada karyawan yang absen sampai jam 08:15';

    public function handle(): void
    {
        $today     = today();
        $sudahAbsen = Attendance::where('date', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->exists();

        if ($sudahAbsen) {
            $this->info('Ada karyawan yang sudah absen. Tidak ada notifikasi.');
            return;
        }

        // Kirim Filament notification ke semua user admin
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::make()
                ->title('Peringatan Absensi!')
                ->body('Tidak ada karyawan yang absen sampai jam 08:15. Mohon segera hubungi karyawan.')
                ->danger()
                ->sendToDatabase($admin);
        }

        $this->info('Notifikasi dikirim ke ' . $admins->count() . ' admin.');
    }
}
