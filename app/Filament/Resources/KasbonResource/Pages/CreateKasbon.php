<?php

namespace App\Filament\Resources\KasbonResource\Pages;

use App\Filament\Resources\KasbonResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKasbon extends CreateRecord
{
    protected static string $resource = KasbonResource::class;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.kasbon.index');
    }
}
