<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AbsensiHariIni extends BaseWidget
{
    protected static ?string $heading = 'Absensi Hari Ini';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'owner']);
    }

    public function table(Table $table): Table
    {
        $today = today();

        return $table
            ->query(
                User::query()
                    ->where('role', 'user')
                    ->where('is_active', true)
                    ->orderBy('name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan'),

                Tables\Columns\TextColumn::make('attendance_today')
                    ->label('Jam Masuk')
                    ->getStateUsing(function ($record) use ($today) {
                        return $record->attendances()
                            ->where('date', $today)
                            ->value('check_in_time') ?? '-';
                    }),

                Tables\Columns\TextColumn::make('status_today')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) use ($today) {
                        return $record->attendances()
                            ->where('date', $today)
                            ->value('status') ?? 'belum_absen';
                    })
                    ->color(fn($state) => match ($state) {
                        'hadir'       => 'success',
                        'terlambat'   => 'warning',
                        'tidak_hadir' => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'hadir'       => 'Hadir',
                        'terlambat'   => 'Terlambat',
                        'tidak_hadir' => 'Tidak Hadir',
                        default       => 'Belum Absen',
                    }),
            ])
            ->paginated(false);
    }
}
