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
    protected static bool $isDiscovered = false;

    // public static function canView(): bool
    // {
    //     if (auth()->user()->role == 'admin') {
    //         return true;
    //     }
    //     return false;
    // }

    protected function getTablePage(): string
    {
        return ListBagipendapatans::class;
    }

    protected function getStats(): array
    {
        $now = now();
        $start = $now->copy()->startOfWeek();
        $end = $now->copy()->endOfWeek();
        
        $stats = [];

        if (auth()->user()->role == 'user') {
            $total_pendapatan = Bagipendapatan::query()
                ->where('user_id', auth()->user()->id)
                ->whereBetween('created_at', [$start, $end])
                // ->whereIn('id', collect($this->getPageTableRecords()->items())->pluck('id'))
                ->get()
                ->sum('bagian_karyawan');

            $stats[] = Stat::make(auth()->user()->name, 'Rp. ' . number_format($total_pendapatan, 0, ',', '.'))
                ->color('success');
        } else {
            $total_pendapatan = Bagipendapatan::query()
                ->whereBetween('created_at', [$start, $end])
                // ->whereIn('id', collect($this->getPageTableRecords()->items())->pluck('id'))
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

            foreach ($total_pendapatan as $pembagian) {
                $stats[] = Stat::make($pembagian['user'], 'Rp. ' . number_format($pembagian['total'], 0, ',', '.'))
                    ->color('success');
            }
        }

        return $stats;
    }
}
