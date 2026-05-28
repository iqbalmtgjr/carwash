<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Bagipendapatan;
use App\Models\Payroll;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePayroll extends Command
{
    protected $signature = 'payroll:generate
                            {--week= : Tanggal awal minggu format Y-m-d, default minggu lalu}
                            {--force : Timpa payroll yang sudah ada}';

    protected $description = 'Generate payroll mingguan untuk semua karyawan aktif';

    public function handle(): void
    {
        $weekInput = $this->option('week');

        if ($weekInput) {
            $weekStart = Carbon::parse($weekInput)->startOfWeek(Carbon::MONDAY);
        } else {
            // Default: minggu lalu Senin s/d Minggu
            $weekStart = now()->subWeek()->startOfWeek(Carbon::MONDAY);
        }

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $this->info("Generate payroll untuk periode: {$weekStart->format('d/m/Y')} — {$weekEnd->format('d/m/Y')}");

        $employees = User::where('role', 'user')
            ->where('is_active', true)
            ->get();

        if ($employees->isEmpty()) {
            $this->warn('Tidak ada karyawan aktif.');
            return;
        }

        $generated = 0;
        $skipped   = 0;

        foreach ($employees as $employee) {
            $exists = Payroll::where('employee_id', $employee->id)
                ->where('week_start', $weekStart->toDateString())
                ->exists();

            if ($exists && ! $this->option('force')) {
                $this->line("  Skip {$employee->name} (sudah ada, gunakan --force untuk timpa)");
                $skipped++;
                continue;
            }

            // 1. Total bagi hasil minggu ini
            $totalShare = (int) Bagipendapatan::where('user_id', $employee->id)
                ->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->sum('bagian_karyawan');

            // 2. Gaji pokok dari profil karyawan
            $baseSalary = (int) ($employee->base_salary ?? 0);

            // 3. Potongan absensi — proporsional dari gaji pokok
            $daysAbsent = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->where('status', 'tidak_hadir')
                ->count();

            $workingDays          = 6; // Senin–Sabtu
            $attendanceDeduction  = $daysAbsent > 0 && $baseSalary > 0
                ? (int) round(($daysAbsent / $workingDays) * $baseSalary)
                : 0;

            // 4. Bonus dari rating bintang 5
            $bonusPerRating = config('attendance.rating_bonus_amount', 2000);
            $ratingCount = Rating::whereHas('transaksi.transaksiuser', function ($q) use ($employee) {
                $q->where('user_id', $employee->id);
            })
                ->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->copy()->endOfDay()])
                ->where('score', 5)
                ->count();
            $bonus = $ratingCount * $bonusPerRating;

            // 5. Total
            $total = $totalShare + $baseSalary - $attendanceDeduction + $bonus;

            Payroll::updateOrCreate(
                ['employee_id' => $employee->id, 'week_start' => $weekStart->toDateString()],
                [
                    'week_end'             => $weekEnd->toDateString(),
                    'total_share'          => $totalShare,
                    'base_salary'          => $baseSalary,
                    'attendance_deduction' => $attendanceDeduction,
                    'bonus'                => $bonus,
                    'total'                => $total,
                ]
            );

            $this->line("  ✓ {$employee->name}: bagi hasil Rp " . number_format($totalShare) .
                " + pokok Rp " . number_format($baseSalary) .
                " - potongan Rp " . number_format($attendanceDeduction) .
                " = Rp " . number_format($total));

            $generated++;
        }

        $this->info("Selesai. {$generated} payroll dibuat, {$skipped} dilewati.");
    }
}
