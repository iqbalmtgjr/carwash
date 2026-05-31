<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Transaksi;
use App\Models\Kendaraan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransaksiResource\Pages;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrows-up-down';
    protected static ?string $navigationLabel = 'Transaksi';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'transaksi';
    protected static ?string $label = 'Transaksi';

    public static function shouldRegisterNavigation(): bool
    {
        if (in_array(auth()->user()->role, ['admin', 'owner'])) {
            return true;
        }

        return false;
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'owner']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->multiple()
                    ->relationship('user', 'name', function (Builder $query) {
                        $query->where('is_active', 1);
                    })
                    ->required()
                    ->label('Pencuci')
                    ->searchable()
                    ->preload()
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                    ]),
                Select::make('layanan_id')
                    ->relationship('layanan', 'nama_layanan')
                    ->options(function () {
                        return \App\Models\Layanan::where('status', 'aktif')->orderBy('nama_layanan')->get()->pluck('formatted_option', 'id');
                    })
                    ->required()
                    ->searchable()
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
                Select::make('kendaraan_id')
                    ->label('Kendaraan')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return Kendaraan::where('plat', 'like', "%{$search}%")
                            ->orWhere('merk', 'like', "%{$search}%")
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn($k) => [
                                $k->id => ($k->plat ? strtoupper($k->plat) : '-') . ' — ' . $k->merk . ' (' . $k->tipe . ')',
                            ])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn($value) => optional(
                        Kendaraan::find($value),
                        fn($k) => ($k->plat ? strtoupper($k->plat) : '-') . ' — ' . $k->merk . ' (' . $k->tipe . ')'
                    ))
                    ->createOptionForm([
                        TextInput::make('plat')
                            ->label('Plat')
                            ->placeholder('Kosongkan jika tidak ada plat')
                            ->dehydrateStateUsing(fn($state) => $state ? strtoupper(trim($state)) : null),
                        Select::make('tipe')
                            ->required()
                            ->label('Tipe')
                            ->options(['mobil' => 'Mobil', 'motor' => 'Motor']),
                        TextInput::make('merk')
                            ->required()
                            ->label('Merk')
                            ->placeholder('Contoh: Agya'),
                        TextInput::make('no_wa')
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                            ->numeric()
                            ->label('No Whatsapp')
                            ->placeholder('Contoh: 081122334455'),
                    ])
                    ->createOptionUsing(fn(array $data) => Kendaraan::create($data)->id)
                    ->required()
                    ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Transaksi::query()->orderBy('id', 'desc'))
            ->columns([
                // TextColumn::make('id')
                //     ->label('ID Transaksi')
                //     ->sortable(),
                TextColumn::make('kendaraan.merk')
                    ->label('Merk')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('layanan.harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Nama Pencuci')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('layanan.nama_layanan')
                    ->label('Nama Layanan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('kendaraan.plat')
                    ->label('Plat')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('rating.score')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) => $state ? str_repeat('★', $state) . str_repeat('☆', 5 - $state) : '-')
                    ->color(fn($state) => match (true) {
                        $state == 5 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0  => 'danger',
                        default     => null,
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('created_today')
                    ->label('Transaksi Hari ini')
                    ->default()
                    ->query(fn(Builder $query) => $query->whereDate('created_at', now()->toDateString())),
                Filter::make('created_this_week')
                    ->label('Transaksi Minggu ini')
                    ->query(fn(Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->actions([
                Tables\Actions\Action::make('kirim_rating')
                    ->label('Kirim Rating')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->url(function ($record) {
                        $noWa = $record->kendaraan?->no_wa;
                        if (! $noWa) {
                            return null;
                        }
                        $ratingUrl = url('/rating/' . $record->id);
                        $pesan = urlencode("Halo! Terima kasih sudah mempercayakan kendaraan Anda ke Mensekak Carwash 🚗✨\n\nMohon berikan rating layanan kami di:\n{$ratingUrl}\n\nTerima kasih 🙏");
                        return "https://wa.me/{$noWa}?text={$pesan}";
                    })
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->kendaraan?->no_wa && ! $record->rating),
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
