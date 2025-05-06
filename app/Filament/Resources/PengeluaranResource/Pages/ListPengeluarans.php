<?php

namespace App\Filament\Resources\PengeluaranResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\Totalpengeluaran;
use App\Filament\Resources\PengeluaranResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListPengeluarans extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = PengeluaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Totalpengeluaran::class
        ];
    }
}
