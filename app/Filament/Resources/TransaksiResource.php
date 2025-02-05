<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Transaksi;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransaksiResource\Pages;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrows-up-down';
    protected static ?string $navigationLabel = 'Transaksi';
    protected static ?string $slug = 'transaksi';
    protected static ?string $label = 'Transaksi';

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
                Select::make('user_id')
                    ->multiple()
                    ->relationship('user', 'name')
                    ->required()
                    ->label('Pencuci')
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                    ]),
                Select::make('layanan_id')
                    ->relationship('layanan', 'nama_layanan')
                    ->options(function () {
                        return \App\Models\Layanan::all()->pluck('formatted_option', 'id');
                    })
                    ->required()
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                    ]),
                DatePicker::make('created_at')
                    ->required()
                    ->default(now())
                    ->label('Tanggal'),
                Repeater::make('kendaraan')
                    ->relationship()
                    ->schema([
                        Select::make('tipe')
                            ->required()
                            ->label('Tipe')
                            ->options([
                                'mobil' => 'Mobil',
                                'motor' => 'Motor',
                            ]),
                        TextInput::make('merk')
                            ->required()
                            ->placeholder('Contoh: Agya')
                            ->label('Merk'),
                        TextInput::make('plat')
                            ->required()
                            ->placeholder('Contoh: KB 000 ER')
                            ->label('Plat'),
                    ])
                    ->columnSpan(2),
                // CreateAction::make()
                //     ->successRedirectUrl(route('filament.admin.resources.transaksi.create')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Transaksi::query()->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Pencuci')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('layanan.nama_layanan')
                    ->label('Nama Layanan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('kendaraan.merk')
                    ->label('Merk')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('kendaraan.plat')
                    ->label('Plat')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('layanan.harga')
                    ->label('Harga')
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
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('created_today')
                    ->label('Transaksi Hari ini')
                    ->query(fn(Builder $query) => $query->whereDate('created_at', now()->toDateString())),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
