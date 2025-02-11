<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Kendaraan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KendaraanResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KendaraanResource\RelationManagers;

class KendaraanResource extends Resource
{
    protected static ?string $model = Kendaraan::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Kendaraan';
    protected static ?string $slug = 'kendaraan';
    protected static ?string $label = 'Kendaraan';
    protected static ?string $navigationGroup = 'Master';

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
                TextInput::make('tipe')
                    ->label('Tipe')
                    ->required(),
                TextInput::make('merk')
                    ->label('Merk')
                    ->required(),
                TextInput::make('plat')
                    ->label('Plat')
                    ->required(),
                TextInput::make('no_wa')
                    ->label('No Whatsapp')
                    ->tel()
                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    ->numeric()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Kendaraan::query()->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->searchable(),
                TextColumn::make('merk')
                    ->label('Merk')
                    ->searchable(),
                TextColumn::make('plat')
                    ->label('Plat')
                    ->searchable(),
                TextColumn::make('no_wa')
                    ->label('No Whatsapp')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Masuk')
                    ->dateTime('d/m/Y')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListKendaraans::route('/'),
            'create' => Pages\CreateKendaraan::route('/create'),
            'edit' => Pages\EditKendaraan::route('/{record}/edit'),
        ];
    }
}
