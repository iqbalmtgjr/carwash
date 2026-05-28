<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

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

            Actions\CreateAction::make()
                ->label('Tambah Manual'),
        ];
    }
}
