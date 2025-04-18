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
use App\Filament\Resources\BagipendapatanResource\Pages;
use Carbon\Carbon;

class BagipendapatanResource extends Resource
{
    protected static ?string $model = Bagipendapatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Bagi Pendapatan';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'bagi-pendapatan';
    protected static ?string $label = 'Bagi Pendapatan';

    public static function shouldRegisterNavigation(): bool
    {
        if (auth()->user()->role == 'admin') {
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
        // Menentukan rentang tanggal seminggu terakhir
        // $startOfWeek = Carbon::now()->subWeek()->startOfDay(); // Mulai dari 7 hari yang lalu
        // $endOfWeek = Carbon::now()->endOfDay(); // Sampai hari ini

        return $table
            // ->defaultPaginationPageOption('all')
            // ->query(
            //     Bagipendapatan::query()
            //         ->whereBetween('created_at', [$startOfWeek, $endOfWeek]) // Filter data seminggu terakhir
            //         ->orderBy('created_at', 'desc')
            // )
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
                Filter::make('created_today')->default(),
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
                    ->default()
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
            ->defaultGroup('transaksi.kendaraan.plat')
            ->groups([
                Group::make('transaksi.kendaraan.merk')
                    ->label('Merk')
                    ->collapsible(),
                Group::make('transaksi.kendaraan.plat')
                    ->label('Plat')
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListBagipendapatans::route('/'),
            'create' => Pages\CreateBagipendapatan::route('/create'),
            // 'edit' => Pages\EditBagipendapatan::route('/{record}/edit'),
        ];
    }
}
