<?php

namespace App\Filament\Widgets;

use App\Models\Kasbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\KasbonResource\Pages\ListKasbons;

class Totalkasbon extends BaseWidget
{
    use InteractsWithPageTable;

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        if (auth()->user()->role == 'admin') {
            return true;
        }
        return false;
    }

    protected function getTablePage(): string
    {
        return ListKasbons::class;
    }

    protected function getStats(): array
    {
        $now = now();
        $start = $now->copy()->startOfDay();
        $end = $now->copy()->endOfDay();

        $kasbon = Kasbon::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('nominal');

        return [
            Stat::make('Total Kasbon Hari Ini', 'Rp. ' . number_format($kasbon, 0, ',', '.'))->color('danger'),
        ];
    }
}
