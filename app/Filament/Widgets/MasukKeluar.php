<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Bagipendapatan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\BagipendapatanResource\Pages\ListBagipendapatans;

class MasukKeluar extends BaseWidget
{
    use InteractsWithPageTable;
    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        if (auth()->user()->id == 1 || auth()->user()->id == 7) {
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
        // dd($pemasukan_bersih);

        $pemasukan_kotor = $lihat_harga->sum('harga');

        $pendapatan_ee = $pemasukan_bersih * 0.5;
        $pendapatan_ivan = $pemasukan_bersih * 0.5;

        return [
            Stat::make('Pemasukan Kotor', 'Rp. ' . number_format($pemasukan_kotor, 0, ',', '.'))->color('success'),
            Stat::make('Pemasukan Bersih Owner', 'Rp. ' . number_format($pemasukan_bersih, 0, ',', '.'))->color('success'),
            Stat::make('Pengeluaran', 'Rp. ' . number_format($pengeluaran, 0, ',', '.'))->color('success'),
            Stat::make('Uang di Tangan', 'Rp. ' . number_format($total_gaji_karyawan + $pendapatan_ee + $pendapatan_ivan, 0, ',', '.'))->color('success'),
            Stat::make('Total Gaji Karyawan', 'Rp. ' . number_format($total_gaji_karyawan, 0, ',', '.'))->color('success'),
            Stat::make('Pemasukan Owner (Ee)', 'Rp. ' . number_format($pendapatan_ee, 0, ',', '.'))->color('success'),
            Stat::make('Pemasukan Owner (Ivan)', 'Rp. ' . number_format($pendapatan_ivan, 0, ',', '.'))->color('success'),
        ];
    }
}
