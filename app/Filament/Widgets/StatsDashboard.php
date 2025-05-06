<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Bagipendapatan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsDashboard extends BaseWidget
{

    protected function getStats(): array
    {
        $now = now();
        $start = $now->copy()->startOfWeek();
        $end = $now->copy()->endOfWeek();
        // dd($start, $end);

        $pengeluaran = Pengeluaran::where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->sum('jumlah');
        // dd($pengeluaran);

        $bagi_pendapatan = Bagipendapatan::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->get();
        // dd($pemasukan_kotor);

        $total_gaji_karyawan = $bagi_pendapatan->sum('bagian_karyawan');
        // dd($total_gaji_karyawan);

        $lihat_harga = $bagi_pendapatan->groupBy('transaksi_id')->map(function ($item) {
            return [
                'transaksi_id' => $item->first()->transaksi_id,
                'layanan' => $item->first()->transaksi->layanan->nama_layanan,
                'harga' => $item->first()->transaksi->layanan->harga,
            ];
        });

        $pemasukan_bersih = $lihat_harga->sum('harga') - $pengeluaran - $total_gaji_karyawan;
        
        $total_pendapatan_karyawanperminggu = Bagipendapatan::query()
                ->where('user_id', auth()->user()->id)
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->get()
                ->sum('bagian_karyawan');
        
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
            // $total_pendapatan = Transaksi::where('user_id', auth()->user()->id)->get();
            $total_pendapatan = Bagipendapatan::query()
                ->where('user_id', auth()->user()->id)
                // ->whereIn('id', collect($this->getPageTableRecords()->items())->pluck('id'))
                ->get();
            // ->sum('bagian_karyawan');
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
                Stat::make('Pendapatan Saya Minggu Ini', 'Rp. ' . number_format($total_pendapatan_karyawanperminggu, 0, ',', '.')),
                // Stat::make('Total Pengeluaran', 'Rp. ' . number_format(collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.'))
                //     ->description(number_format(collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.') . ' meningkat')
                //     ->descriptionIcon('heroicon-m-arrow-trending-up')
                //     ->chart($pengeluaran_per_hari)
                //     ->color('info'),
                Stat::make('Total Pendapatan Saya Selama Bekerja', 'Rp. ' . number_format(collect($total_pendapatan)->sum('bagian_karyawan'), 0, ',', '.'))
                    ->description(number_format(collect($total_pendapatan)->sum('bagian_karyawan') - collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.') . ' meningkat')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart($pendapatan_per_hari),
            ];
        } else {
            return [
                Stat::make('Pendapatan Bersih Minggu Ini', 'Rp. ' . number_format($pemasukan_bersih, 0, ',', '.')),
                Stat::make('Total PenPendapatan Bersihgeluaran', 'Rp. ' . number_format(collect($total_pengeluaran)->sum('jumlah'), 0, ',', '.'))
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
