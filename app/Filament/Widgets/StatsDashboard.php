<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Bagipendapatan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsDashboard extends BaseWidget
{
    // public function getHeaderWidgetsColumns(): int | array
    // {
    //     return [
    //         'sm' => 4,
    //         'md' => 4,
    //         'xl' => 5,
    //     ];
    // }

    // public function getHeaderWidgetsColumns(): int | array
    // {
    //     return 9;
    // }

    protected function getStats(): array
    {
        $get_layanan = Transaksi::count();

        $total_pendapatan = Transaksi::get();
        $pendapatan_per_hari = [];
        foreach ($total_pendapatan as $pendapatan) {
            $pendapatan_per_hari[] = $pendapatan->created_at->format('Y-m-d');
        }
        $pendapatan_per_hari = array_count_values($pendapatan_per_hari);
        $pendapatan_per_hari = array_map(function ($value) {
            return $value;
        }, $pendapatan_per_hari);

        if (auth()->user()->role == 'user') {
            $total_pendapatan = Bagipendapatan::query()
                ->where('user_id', auth()->user()->id)
                ->get();
        }

        $total_pengeluaran = Pengeluaran::get();
        $pengeluaran_per_hari = [];
        foreach ($total_pengeluaran as $pengeluaran) {
            $pengeluaran_per_hari[] = $pengeluaran->created_at->format('Y-m-d');
        }
        $pengeluaran_per_hari = array_count_values($pengeluaran_per_hari);
        $pengeluaran_per_hari = array_map(function ($value) {
            return $value;
        }, $pengeluaran_per_hari);

        if (auth()->user()->role == 'user') {
            return [
                Stat::make('Total Transaksi', $get_layanan),
                Stat::make('Total Pendapatan', 'Rp. ' . number_format(collect($total_pendapatan)->sum('bagian_karyawan'), 0, ',', '.'))
                    ->description(number_format(collect($total_pendapatan)->sum('bagian_karyawan') - collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.') . ' meningkat')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart($pendapatan_per_hari)
                    ->color('success'),
            ];
        } else {
            return [
                Stat::make('Total Transaksi', $get_layanan),
                Stat::make('Total Pengeluaran', 'Rp. ' . number_format(collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.'))
                    ->description(number_format(collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.') . ' meningkat')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart($pengeluaran_per_hari)
                    ->color('info'),
                Stat::make('Total Pendapatan', 'Rp. ' . number_format(collect($total_pendapatan)->sum('layanan.harga'), 0, ',', '.'))
                    ->description(number_format(collect($total_pendapatan)->sum('layanan.harga') - collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.') . ' meningkat')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart($pendapatan_per_hari)
                    ->color('success'),
            ];
        }
    }
}
