<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Kelola User';
    protected static ?string $slug = 'kelola-user';
    protected static ?string $label = 'Kelola User';
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
                TextInput::make('name')
                    ->required()
                    ->label('Nama')
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('no_wa')
                    ->required()
                    ->tel()
                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    ->label('Nomor WhatsApp')
                    ->numeric()
                    ->maxLength(20),
                Select::make('role')
                    ->required()
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ])
                    ->default('user')
                    ->label('Role'),
                TextInput::make('alamat')
                    ->required()
                    ->label('Alamat')
                    ->maxLength(255),
                Select::make('is_active')
                    ->required()
                    ->default(1)
                    ->options([
                        0 => 'Tidak Aktif',
                        1 => 'Aktif',
                    ])
                    ->label('Status Akun'),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->revealable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::query()->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('no_wa')
                    ->label('No WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Belum ada data.'),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum ada data.')
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'success',
                        'user' => 'info',
                    }),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger',
                    })
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn(int $state): string => $state === 1 ? 'Aktif' : 'Tidak Aktif'),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status Akun')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ])
                    ->default('1'),
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}