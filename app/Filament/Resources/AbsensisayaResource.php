<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensisayaResource\Pages;
use App\Models\Attendance;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AbsensisayaResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Absensi Saya';

    protected static ?string $pluralModelLabel = 'Absensi Saya';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'user';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'user';
    }

    // Query hanya record milik user yang login
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('employee_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Jam Masuk')
                    ->default('-'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'hadir',
                        'warning' => 'terlambat',
                        'danger'  => 'tidak_hadir',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'hadir'       => 'Hadir',
                        'terlambat'   => 'Terlambat',
                        'tidak_hadir' => 'Tidak Hadir',
                        default       => $state,
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn($query) => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->default(),

                Tables\Filters\Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn($query) => $query->whereMonth('date', now()->month)->whereYear('date', now()->year)),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsensisaya::route('/'),
        ];
    }
}
