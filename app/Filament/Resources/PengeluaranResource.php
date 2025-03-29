<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Pengeluaran;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PengeluaranResource\Pages;
use App\Filament\Resources\PengeluaranResource\RelationManagers;

class PengeluaranResource extends Resource
{
    protected static ?string $model = Pengeluaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Pengeluaran';
    protected static ?string $slug = 'pengeluaran';
    protected static ?string $label = 'Pengeluaran';

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
                TextInput::make('pengeluaran')
                    ->required()
                    ->placeholder('Contoh: Beli Sabun B29')
                    ->label('Pengeluaran'),
                TextInput::make('jumlah')
                    ->required()
                    ->placeholder('Tanpa titik. Contoh: 50000')
                    ->numeric()
                    ->label('Jumlah Pengeluaran'),
                TextInput::make('keterangan')
                    ->label('Keterangan'),
                DatePicker::make('created_at')
                    ->required()
                    ->default(now())
                    ->label('Tanggal'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Pengeluaran::query()->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('pengeluaran')
                    ->label('Pengeluaran')
                    ->searchable(),
                TextColumn::make('jumlah')
                    ->label('Jumlah Pengeluaran')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengeluaran')
                    ->date('d/m/Y')
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
                    ->default()
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
            'index' => Pages\ListPengeluarans::route('/'),
            'create' => Pages\CreatePengeluaran::route('/create'),
            'edit' => Pages\EditPengeluaran::route('/{record}/edit'),
        ];
    }
}
