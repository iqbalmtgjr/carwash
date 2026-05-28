<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Penggajian';

    protected static ?string $pluralModelLabel = 'Penggajian';

    protected static ?string $modelLabel = 'Penggajian';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('employee_id')
                ->label('Karyawan')
                ->options(User::where('role', 'user')->where('is_active', true)->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\DatePicker::make('week_start')
                ->label('Awal Minggu (Senin)')
                ->required(),

            Forms\Components\DatePicker::make('week_end')
                ->label('Akhir Minggu (Minggu)')
                ->required(),

            Forms\Components\TextInput::make('total_share')
                ->label('Bagi Hasil')
                ->numeric()
                ->prefix('Rp')
                ->default(0)
                ->required(),

            Forms\Components\TextInput::make('base_salary')
                ->label('Gaji Pokok')
                ->numeric()
                ->prefix('Rp')
                ->default(0)
                ->required(),

            Forms\Components\TextInput::make('attendance_deduction')
                ->label('Potongan Absensi')
                ->numeric()
                ->prefix('Rp')
                ->default(0),

            Forms\Components\TextInput::make('bonus')
                ->label('Bonus')
                ->numeric()
                ->prefix('Rp')
                ->default(0),

            Forms\Components\TextInput::make('total')
                ->label('Total Gaji')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            Forms\Components\Textarea::make('catatan')
                ->label('Catatan')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('week_start')
                    ->label('Periode')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->week_start->format('d/m') . ' – ' . $record->week_end->format('d/m/Y')
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_share')
                    ->label('Bagi Hasil')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('attendance_deduction')
                    ->label('Potongan')
                    ->formatStateUsing(fn($state) => $state > 0 ? '- Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->color(fn($state) => $state > 0 ? 'danger' : null),

                Tables\Columns\TextColumn::make('bonus')
                    ->label('Bonus')
                    ->formatStateUsing(fn($state) => $state > 0 ? '+ Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->color(fn($state) => $state > 0 ? 'success' : null),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->weight('bold')
                    ->sortable(),
            ])
            ->defaultSort('week_start', 'desc')
            ->filters([
                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn($query) => $query->where('week_start', now()->startOfWeek()->toDateString()))
                    ->default(),

                Tables\Filters\Filter::make('last_week')
                    ->label('Minggu Lalu')
                    ->query(fn($query) => $query->where('week_start', now()->subWeek()->startOfWeek()->toDateString())),

                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Karyawan')
                    ->options(User::where('role', 'user')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit'   => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
