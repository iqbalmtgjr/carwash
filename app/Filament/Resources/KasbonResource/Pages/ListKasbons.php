<?php

namespace App\Filament\Resources\KasbonResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\Totalkasbon;
use App\Filament\Resources\KasbonResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListKasbons extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = KasbonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Totalkasbon::class
        ];
    }
}
