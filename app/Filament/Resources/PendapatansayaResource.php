<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Bagipendapatan;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PendapatansayaResource\Pages;

class PendapatansayaResource extends Resource
{
    protected static ?string $model = Bagipendapatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pendapatan Saya';
    protected static ?string $slug = 'pendapatan-saya';
    protected static ?string $label = 'Pendapatan Saya';

    public static function shouldRegisterNavigation(): bool
    {
        if (auth()->user()->role == 'user') {
            return true;
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
           // ->defaultPaginationPageOption('all')
            ->query(Bagipendapatan::query()->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pencuci')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('transaksi.layanan.nama_layanan')
                    ->label('Nama Layanan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('transaksi.kendaraan.merk')
                    ->label('Merk')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('transaksi.kendaraan.plat')
                    ->label('Plat')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bagian_karyawan')
                    ->label('Bagian Karyawan')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                array_key_exists('created_from', $data) ? $data['created_from'] : null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                array_key_exists('created_until', $data) ? $data['created_until'] : null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('created_today')
                    ->label('Transaksi Hari ini')
                    ->query(fn(Builder $query) => $query->whereDate('created_at', now()->toDateString())),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // ->defaultGroup('transaksi.kendaraan.plat')
            ->groups([
                Group::make('transaksi.kendaraan.merk')
                    ->label('Merk')
                    ->collapsible(),
                Group::make('transaksi.kendaraan.plat')
                    ->label('Plat')
                    ->collapsible(),
            ]);
        // ->groupsOnly();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendapatansayas::route('/'),
            // 'create' => Pages\CreatePendapatansaya::route('/create'),
            // 'edit' => Pages\EditPendapatansaya::route('/{record}/edit'),
        ];
    }
}
