<?php

namespace App\Filament\Resources\AbsensisayaResource\Pages;

use App\Filament\Resources\AbsensisayaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAbsensisayas extends ListRecords
{
    protected static string $resource = AbsensisayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
