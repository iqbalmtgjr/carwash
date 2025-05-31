<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Kasbon;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KasbonResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KasbonResource\RelationManagers;

class KasbonResource extends Resource
{
    protected static ?string $model = Kasbon::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Kasbon';
    protected static ?string $slug = 'Kasbon';
    protected static ?string $label = 'Kasbon';

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
                    ->required()
                    ->options(User::where('is_active', true)->pluck('name', 'id'))
                    ->label('Karyawan'),
                TextInput::make('nominal')
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
            ->query(Kasbon::query()->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable(),
                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Kasbon')
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
            'index' => Pages\ListKasbons::route('/'),
            'create' => Pages\CreateKasbon::route('/create'),
            'edit' => Pages\EditKasbon::route('/{record}/edit'),
        ];
    }
}
