<?php

namespace App\Filament\Widgets;

use App\Models\Kasbon;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Bagipendapatan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\BagipendapatanResource\Pages\ListBagipendapatans;
use Carbon\Carbon;

class MasukKeluar extends BaseWidget
{
    use InteractsWithPageTable;
    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        if (auth()->user()->id == 1) {
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
        // Ambil query dari tabel dengan filter yang aktif
        $query = $this->getPageTableQuery();
        
        // Clone query untuk mendapatkan ID yang terfilter
        $filteredIds = $query->pluck('id');
        
        // Jika tidak ada data yang terfilter, gunakan default (minggu ini)
        if ($filteredIds->isEmpty()) {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            
            $bagi_pendapatan = Bagipendapatan::query()
                ->whereBetween('created_at', [$start, $end])
                ->get();
        } else {
            // Gunakan data yang sudah terfilter
            $bagi_pendapatan = Bagipendapatan::query()
                ->whereIn('id', $filteredIds)
                ->get();
            
            // Ambil rentang tanggal dari data yang terfilter
            $start = $bagi_pendapatan->min('created_at');
            $end = $bagi_pendapatan->max('created_at');
            
            // Jika start dan end null, gunakan hari ini
            if (!$start || !$end) {
                $start = now()->startOfDay();
                $end = now()->endOfDay();
            } else {
                $start = Carbon::parse($start)->startOfDay();
                $end = Carbon::parse($end)->endOfDay();
            }
        }

        // Hitung pemasukan kotor
        $lihat_harga = $bagi_pendapatan->groupBy('transaksi_id')->map(function ($item) {
            return [
                'transaksi_id' => $item->first()->transaksi_id,
                'layanan' => $item->first()->transaksi->layanan->nama_layanan,
                'harga' => $item->first()->transaksi->layanan->harga,
            ];
        });

        $pemasukan_kotor = $lihat_harga->sum('harga');

        // Hitung pengeluaran berdasarkan rentang tanggal
        $pengeluaran = Pengeluaran::whereBetween('created_at', [$start, $end])
            ->sum('jumlah');

        // Hitung selisih
        $selisih = $pemasukan_kotor - $pengeluaran;

        return [
            Stat::make('Pemasukan Kotor', 'Rp. ' . number_format($pemasukan_kotor, 0, ',', '.'))
                ->color('success')
                ->description('Total pendapatan sebelum dikurangi')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Pengeluaran', 'Rp. ' . number_format($pengeluaran, 0, ',', '.'))
                ->color('danger')
                ->description('Total pengeluaran periode ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down'),
            Stat::make('Selisih', 'Rp. ' . number_format($selisih, 0, ',', '.'))
                ->color($selisih >= 0 ? 'success' : 'danger')
                ->description($selisih >= 0 ? 'Surplus' : 'Defisit')
                ->descriptionIcon($selisih >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'),
        ];
    }
}