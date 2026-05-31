<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Layanan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
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
        return auth()->user()?->role === 'owner';
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'owner']);
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
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'aktif'       => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                    ])
                    ->default('aktif')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption('all')
            ->query(Layanan::query()->orderBy('status', 'asc')->orderBy('nama_layanan', 'asc'))
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'aktif' ? 'success' : 'danger')
                    ->formatStateUsing(fn(string $state): string => $state === 'aktif' ? 'Aktif' : 'Tidak Aktif'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif'       => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('toggleStatus')
                    ->label(fn(Layanan $record): string => $record->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn(Layanan $record): string => $record->status === 'aktif' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(Layanan $record): string => $record->status === 'aktif' ? 'danger' : 'success')
                    ->action(fn(Layanan $record) => $record->update([
                        'status' => $record->status === 'aktif' ? 'tidak_aktif' : 'aktif',
                    ])),
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
