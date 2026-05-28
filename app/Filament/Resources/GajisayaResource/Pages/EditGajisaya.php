<?php

namespace App\Filament\Resources\GajisayaResource\Pages;

use App\Filament\Resources\GajisayaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGajisaya extends EditRecord
{
    protected static string $resource = GajisayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
