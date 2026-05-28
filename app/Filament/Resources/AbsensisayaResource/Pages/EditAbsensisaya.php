<?php

namespace App\Filament\Resources\AbsensisayaResource\Pages;

use App\Filament\Resources\AbsensisayaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbsensisaya extends EditRecord
{
    protected static string $resource = AbsensisayaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
