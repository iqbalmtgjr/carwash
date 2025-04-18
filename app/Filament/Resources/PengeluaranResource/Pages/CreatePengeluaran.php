<?php

namespace App\Filament\Resources\PengeluaranResource\Pages;

use App\Filament\Resources\PengeluaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengeluaran extends CreateRecord
{
    protected static string $resource = PengeluaranResource::class;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.pengeluaran.index');
    }
}
