<?php

namespace App\Filament\Widgets;

use App\Models\Pengeluaran;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\PengeluaranResource\Pages\ListPengeluarans;

class Totalpengeluaran extends BaseWidget
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
        return ListPengeluarans::class;
    }

    protected function getStats(): array
    {
        $now = now();
        $start = $now->copy()->startOfDay();
        $end = $now->copy()->endOfDay();

        $pengeluaran_hariini = Pengeluaran::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('jumlah');
            
        $pengeluaran = Pengeluaran::query()
            // ->whereIn('id', collect($this->getPageTableRecords()->items())->pluck('id'))
            ->whereBetween('created_at', [$this->getPageTableRecords()->min('created_at'), $this->getPageTableRecords()->max('created_at')])
            ->sum('jumlah');

        return [
            Stat::make('Total Pengeluaran Hari Ini', 'Rp. ' . number_format($pengeluaran_hariini, 0, ',', '.'))->color('danger'),
            Stat::make('Total Pengeluaran Filter', 'Rp. ' . number_format($pengeluaran, 0, ',', '.'))->color('danger'),
        ];
    }
}
