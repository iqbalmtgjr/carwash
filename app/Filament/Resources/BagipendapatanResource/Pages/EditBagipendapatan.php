<?php

namespace App\Filament\Resources\BagipendapatanResource\Pages;

use App\Filament\Resources\BagipendapatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBagipendapatan extends EditRecord
{
    protected static string $resource = BagipendapatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
