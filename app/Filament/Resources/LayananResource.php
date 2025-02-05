<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Layanan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LayananResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LayananResource\RelationManagers;

class LayananResource extends Resource
{
    protected static ?string $model = Layanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Layanan';
    protected static ?string $slug = 'layanan';
    protected static ?string $label = 'Layanan';
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
                TextInput::make('nama_layanan')
                    ->required()
                    ->placeholder('Contoh: Cuci Mobil')
                    ->label('Layanan'),
                TextInput::make('harga')
                    ->required()
                    ->placeholder('Tanpa titik. Contoh: 50000')
                    ->numeric()
                    ->label('Harga'),
                TextInput::make('bagi_karyawan')
                    ->required()
                    ->placeholder('Tanpa titik. Contoh: 20000')
                    ->numeric()
                    ->label('Pembagian ke Karyawan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Layanan::query()->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('nama_layanan')
                    ->label('Layanan')
                    ->searchable(),
                TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bagi_karyawan')
                    ->label('Pembagian ke Karyawan')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListLayanans::route('/'),
            'create' => Pages\CreateLayanan::route('/create'),
            'edit' => Pages\EditLayanan::route('/{record}/edit'),
        ];
    }
}
