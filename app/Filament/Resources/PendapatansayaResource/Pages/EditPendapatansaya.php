<?php

namespace App\Filament\Resources\PendapatansayaResource\Pages;

use App\Filament\Resources\PendapatansayaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendapatansaya extends EditRecord
{
    protected static string $resource = PendapatansayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
