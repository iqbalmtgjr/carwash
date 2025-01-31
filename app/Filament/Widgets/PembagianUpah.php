<?php

namespace App\Filament\Widgets;

use App\Models\Bagipendapatan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\BagipendapatanResource\Pages\ListBagipendapatans;

class PembagianUpah extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getHidden(): bool
    {
        return request()->routeIs('admin');
    }

    protected function getTablePage(): string
    {
        return ListBagipendapatans::class;
    }

    protected function getStats(): array
    {
        $total_pendapatan = Bagipendapatan::query()
            ->whereIn('id', collect($this->getPageTableRecords()->items())->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(function ($group) {
                return [
                    'user' => $group->first()->user->name,
                    'total' => $group->sum('bagian_karyawan'),
                ];
            })
            ->sortByDesc('total')
            ->values();


        $stats = [];
        foreach ($total_pendapatan as $pembagian) {
            $stats[] = Stat::make($pembagian['user'], 'Rp. ' . number_format($pembagian['total'], 0, ',', '.'))
                ->color('success');
        }

        return $stats;
    }
}
