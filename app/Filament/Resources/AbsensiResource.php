<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Attendance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AbsensiResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Absensi';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'owner']);
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'owner']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('employee_id')
                ->label('Karyawan')
                ->options(User::where('is_active', true)->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->default(today()),

            Forms\Components\TimePicker::make('check_in_time')
                ->label('Jam Masuk')
                ->seconds(false),

            Forms\Components\TimePicker::make('check_out_time')
                ->label('Jam Keluar')
                ->seconds(false),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'hadir'       => 'Hadir',
                    'terlambat'   => 'Terlambat',
                    'tidak_hadir' => 'Tidak Hadir',
                ])
                ->required()
                ->default('hadir'),

            Forms\Components\TextInput::make('device_info')
                ->label('Info Perangkat')
                ->disabled(),
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'hadir'       => 'Hadir',
                        'terlambat'   => 'Terlambat',
                        'tidak_hadir' => 'Tidak Hadir',
                    ]),

                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn($query) => $query->whereDate('date', today()))
                    ->default(),

                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn($query) => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])),
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
            'index'  => Pages\ListAbsensi::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit'   => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
