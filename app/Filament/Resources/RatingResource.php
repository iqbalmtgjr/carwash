<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatingResource\Pages;
use App\Models\Rating;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Rating Pelanggan';

    protected static ?string $pluralModelLabel = 'Rating Pelanggan';

    protected static ?string $modelLabel = 'Rating';

    protected static ?int $navigationSort = 3;

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
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaksi.kendaraan.merk')
                    ->label('Kendaraan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('transaksi.kendaraan.plat')
                    ->label('Plat'),

                Tables\Columns\TextColumn::make('score')
                    ->label('Bintang')
                    ->formatStateUsing(fn($state) => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->color(fn($state) => match (true) {
                        $state == 5 => 'success',
                        $state >= 3 => 'warning',
                        default     => 'danger',
                    }),

                Tables\Columns\TextColumn::make('komentar')
                    ->label('Komentar')
                    ->wrap()
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('bintang_5')
                    ->label('Bintang 5')
                    ->query(fn($query) => $query->where('score', 5)),

                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn($query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
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
            'index' => Pages\ListRatings::route('/'),
        ];
    }
}
