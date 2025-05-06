<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use App\Models\Pengeluaran;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\TransaksiResource\Pages\ListTransaksis;

class Totaltransaksi extends BaseWidget
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
        return ListTransaksis::class;
    }

    protected function getStats(): array
    {
        $now = now();
        $start = $now->copy()->startOfWeek();
        $end = $now->copy()->endOfWeek();
        
        $total_transaksi = Transaksi::query()
            ->whereBetween('created_at', [$start, $end])
            // ->whereIn('id', collect($this->getPageTableRecords()->items())->pluck('id'))
            ->get()
            ->sum(fn(Transaksi $transaksi) => $transaksi->layanan->harga);

        $pengeluaran = Pengeluaran::query()
            ->whereBetween('created_at', [$start, $end])
            // ->whereBetween('created_at', [$this->getPageTableRecords()->min('created_at'), $this->getPageTableRecords()->max('created_at')])
            ->sum('jumlah');

        $net_transaksi = $total_transaksi - $pengeluaran;

        $total_transaksi_hariini = Transaksi::query()
            ->whereIn('id', collect($this->getPageTableRecords()->items())->pluck('id'))
            ->get()
            ->sum(fn(Transaksi $transaksi) => $transaksi->layanan->harga);

        $pengeluaran_hariini = Pengeluaran::query()
            ->whereBetween('created_at', [$this->getPageTableRecords()->min('created_at'), $this->getPageTableRecords()->max('created_at')])
            ->sum('jumlah');

        $net_transaksi_hariini = $total_transaksi_hariini - $pengeluaran_hariini;

        return [
            Stat::make('Total Transaksi Hari Ini', 'Rp. ' . number_format($total_transaksi_hariini, 0, ',', '.'))->color('success'),
            Stat::make('Pengeluaran Hari Ini', 'Rp. ' . number_format($pengeluaran_hariini, 0, ',', '.'))->color('danger'),
            Stat::make('Net Transaksi Hari Ini', 'Rp. ' . number_format($net_transaksi_hariini, 0, ',', '.'))->color('primary'),
            Stat::make('Total Transaksi', 'Rp. ' . number_format($total_transaksi, 0, ',', '.'))->color('success'),
            Stat::make('Pengeluaran', 'Rp. ' . number_format($pengeluaran, 0, ',', '.'))->color('danger'),
            Stat::make('Net Transaksi', 'Rp. ' . number_format($net_transaksi, 0, ',', '.'))->color('primary'),
        ];
    }
}
