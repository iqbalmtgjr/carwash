<?php

namespace App\Filament\Resources\PendapatansayaResource\Widgets;

use App\Models\Bagipendapatan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\BagipendapatanResource\Pages\ListBagipendapatans;

class PendapatanUser extends BaseWidget
{
    use InteractsWithPageTable;

    public static function canView(): bool
    {
        if (auth()->user()->role == 'user') {
            return true;
        }
        return false;
    }


    protected function getTablePage(): string
    {
        return ListBagipendapatans::class;
    }

    protected function getStats(): array
    {
        $stats = [];

        $total_pendapatan = Bagipendapatan::query()
            ->where('user_id', auth()->user()->id)
            ->get()
            ->sum('bagian_karyawan');

        $stats[] = Stat::make(auth()->user()->name, 'Rp. ' . number_format($total_pendapatan, 0, ',', '.'))
            ->color('success');

        return $stats;
    }
}
