<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GajisayaResource\Pages;
use App\Models\Payroll;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GajisayaResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Gaji Saya';

    protected static ?string $pluralModelLabel = 'Gaji Saya';

    protected static ?string $modelLabel = 'Gaji';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'user';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'user';
    }

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
                Tables\Columns\TextColumn::make('week_start')
                    ->label('Periode')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->week_start->format('d/m') . ' – ' . $record->week_end->format('d/m/Y')
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_share')
                    ->label('Bagi Hasil')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('attendance_deduction')
                    ->label('Pot. Absensi')
                    ->formatStateUsing(fn($state) => $state > 0 ? '- Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->color(fn($state) => $state > 0 ? 'danger' : null),

                Tables\Columns\TextColumn::make('kasbon_deduction')
                    ->label('Pot. Kasbon')
                    ->formatStateUsing(fn($state) => $state > 0 ? '- Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->color(fn($state) => $state > 0 ? 'danger' : null),

                Tables\Columns\TextColumn::make('bonus')
                    ->label('Bonus')
                    ->formatStateUsing(fn($state) => $state > 0 ? '+ Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->color(fn($state) => $state > 0 ? 'success' : null),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total Diterima')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->weight('bold'),
            ])
            ->defaultSort('week_start', 'desc')
            ->filters([
                Tables\Filters\Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn($query) => $query->whereMonth('week_start', now()->month)->whereYear('week_start', now()->year))
                    ->default(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGajisayas::route('/'),
        ];
    }
}
