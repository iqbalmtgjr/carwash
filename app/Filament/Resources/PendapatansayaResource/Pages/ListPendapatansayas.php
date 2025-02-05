<?php

namespace App\Filament\Resources\PendapatansayaResource\Pages;

use Filament\Actions;
use App\Filament\Widgets\PembagianUpah;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PendapatansayaResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListPendapatansayas extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = PendapatansayaResource::class;

    protected function getHeaderActions(): array
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
