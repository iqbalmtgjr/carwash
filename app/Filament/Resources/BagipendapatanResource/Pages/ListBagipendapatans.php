<?php

namespace App\Filament\Resources\BagipendapatanResource\Pages;

use Filament\Actions;
use App\Filament\Widgets\PembagianUpah;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\BagipendapatanResource;
use App\Models\Bagipendapatan;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListBagipendapatans extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = BagipendapatanResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PembagianUpah::class,
        ];
    }
}
