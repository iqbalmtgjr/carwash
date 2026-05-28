<?php

namespace App\Filament\Resources\AbsensisayaResource\Pages;

use App\Filament\Resources\AbsensisayaResource;
use Filament\Resources\Pages\ListRecords;

class ListAbsensisaya extends ListRecords
{
    protected static string $resource = AbsensisayaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
