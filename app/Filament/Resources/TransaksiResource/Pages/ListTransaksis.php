<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TransaksiResource;
use App\Filament\Widgets\Totaltransaksi;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListTransaksis extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Totaltransaksi::class
        ];
    }
}
