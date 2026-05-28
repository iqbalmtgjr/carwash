<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_payroll')
                ->label('Generate Payroll')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->form([
                    DatePicker::make('week_start')
                        ->label('Pilih Senin awal minggu')
                        ->default(now()->subWeek()->startOfWeek()->toDateString())
                        ->required(),
                ])
                ->action(function (array $data) {
                    Artisan::call('payroll:generate', [
                        '--week'  => $data['week_start'],
                        '--force' => true,
                    ]);

                    $output = Artisan::output();

                    Notification::make()
                        ->title('Payroll berhasil di-generate!')
                        ->body(strip_tags($output))
                        ->success()
                        ->send();
                }),

            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $records = \App\Models\Payroll::with('employee')
                        ->orderBy('week_start', 'desc')
                        ->get();

                    $csv = "Karyawan,Periode,Bagi Hasil,Gaji Pokok,Potongan,Bonus,Total\n";
                    foreach ($records as $r) {
                        $csv .= implode(',', [
                            '"' . $r->employee->name . '"',
                            $r->week_start->format('d/m/Y') . ' - ' . $r->week_end->format('d/m/Y'),
                            $r->total_share,
                            $r->base_salary,
                            $r->attendance_deduction,
                            $r->bonus,
                            $r->total,
                        ]) . "\n";
                    }

                    return Response::streamDownload(
                        fn() => print($csv),
                        'payroll-' . now()->format('Y-m-d') . '.csv',
                        ['Content-Type' => 'text/csv']
                    );
                }),

            Actions\CreateAction::make()
                ->label('Tambah Manual'),
        ];
    }
}
