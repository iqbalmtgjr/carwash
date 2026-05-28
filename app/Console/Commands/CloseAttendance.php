<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Console\Command;

class CloseAttendance extends Command
{
    protected $signature = 'attendance:close';

    protected $description = 'Tutup absensi harian — tandai karyawan yang tidak hadir';

    public function handle(): void
    {
        $today = today();

        $employees = User::where('role', 'user')
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($employees as $employee) {
            $exists = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->exists();

            if (! $exists) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date'        => $today,
                    'status'      => 'tidak_hadir',
                ]);
                $count++;
            }
        }

        $this->info("Absensi ditutup. {$count} karyawan ditandai tidak hadir.");
    }
}
