<?php

namespace App\Filament\Resources\GajisayaResource\Pages;

use App\Filament\Resources\GajisayaResource;
use Filament\Resources\Pages\ListRecords;

class ListGajisayas extends ListRecords
{
    protected static string $resource = GajisayaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
