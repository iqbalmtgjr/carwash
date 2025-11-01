<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Bagipendapatan;
use App\Models\Kasbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;

class StatsDashboard extends BaseWidget
{
    // Polling untuk auto refresh setiap 30 detik
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $now = now();
        $start = $now->copy()->startOfWeek();
        $end = $now->copy()->endOfWeek();

        // Pengeluaran mingguan
        $pengeluaran_mingguan = Pengeluaran::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('jumlah');

        // Bagi pendapatan mingguan
        $bagi_pendapatan = Bagipendapatan::query()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $total_gaji_karyawan = $bagi_pendapatan->sum('bagian_karyawan');

        // Hitung pemasukan kotor
        $lihat_harga = $bagi_pendapatan->groupBy('transaksi_id')->map(function ($item) {
            return [
                'transaksi_id' => $item->first()->transaksi_id,
                'layanan' => $item->first()->transaksi->layanan->nama_layanan,
                'harga' => $item->first()->transaksi->layanan->harga,
            ];
        });

        $pemasukan_kotor_mingguan = $lihat_harga->sum('harga');
        $pemasukan_bersih = $pemasukan_kotor_mingguan - $pengeluaran_mingguan - $total_gaji_karyawan;

        // Data untuk user (karyawan)
        if (auth()->user()->role == 'user') {
            // Kasbon karyawan (semua waktu)
            $kasbonperkaryawan = Kasbon::where('user_id', auth()->user()->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('nominal');

            // Gaji karyawan minggu ini
            $gajiperorang = Bagipendapatan::query()
                ->where('user_id', auth()->user()->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('bagian_karyawan');

            // Kasbon minggu ini
            $kasbon_minggu_ini = Kasbon::where('user_id', auth()->user()->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('nominal');

            $total_pendapatan_karyawanperminggu = $gajiperorang - $kasbon_minggu_ini;

            // Total pendapatan selama bekerja
            $total_pendapatan_seluruhnya = Bagipendapatan::query()
                ->where('user_id', auth()->user()->id)
                ->sum('bagian_karyawan');

            // Chart data untuk pendapatan karyawan (7 hari terakhir)
            $pendapatan_chart = $this->getChartData('user');

            return [
                Stat::make('Pendapatan Minggu Ini', 'Rp. ' . number_format($total_pendapatan_karyawanperminggu, 0, ',', '.'))
                    ->description('Setelah dikurangi kasbon minggu ini')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success')
                    ->chart($pendapatan_chart),
                
                Stat::make('Total Kasbon', 'Rp. ' . number_format($kasbonperkaryawan, 0, ',', '.'))
                    ->description('Kasbon anda minggu ini')
                    ->descriptionIcon('heroicon-m-credit-card')
                    ->color('warning'),
                
                Stat::make('Total Pendapatan Selama Bekerja', 'Rp. ' . number_format($total_pendapatan_seluruhnya, 0, ',', '.'))
                    ->description('Pendapatan kotor (sebelum kasbon)')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('info')
                    ->chart($pendapatan_chart),
            ];
        } else {
            // Admin view
            // Chart data (7 hari terakhir)
            $pendapatan_chart = $this->getChartData('admin', 'pendapatan');
            $pengeluaran_chart = $this->getChartData('admin', 'pengeluaran');

            // Total pendapatan dan pengeluaran (all time)
            $total_pendapatan_all = Transaksi::with('layanan')->get()->sum('layanan.harga');
            $total_pengeluaran_all = Pengeluaran::sum('jumlah');

            return [
                Stat::make('Pendapatan Bersih Minggu Ini', 'Rp. ' . number_format($pemasukan_bersih, 0, ',', '.'))
                    ->description('Pemasukan - Pengeluaran - Gaji Karyawan')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success')
                    ->chart($pendapatan_chart),
                
                Stat::make('Total Pengeluaran Minggu Ini', 'Rp. ' . number_format($pengeluaran_mingguan, 0, ',', '.'))
                    ->description('Total pengeluaran operasional')
                    ->descriptionIcon('heroicon-m-arrow-trending-down')
                    ->color('danger')
                    ->chart($pengeluaran_chart),
                
                Stat::make('Total Gaji Karyawan Minggu Ini', 'Rp. ' . number_format($total_gaji_karyawan, 0, ',', '.'))
                    ->description('Total bagian karyawan')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('info'),
                
                Stat::make('Total Pendapatan (All Time)', 'Rp. ' . number_format($total_pendapatan_all, 0, ',', '.'))
                    ->description('Selisih: Rp. ' . number_format($total_pendapatan_all - $total_pengeluaran_all, 0, ',', '.'))
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success')
                    ->chart($pendapatan_chart),
            ];
        }
    }

    /**
     * Generate chart data untuk 7 hari terakhir
     */
    private function getChartData(string $role, string $type = 'pendapatan'): array
    {
        $chartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            if ($role == 'user') {
                // Chart untuk karyawan (pendapatan per hari)
                $value = Bagipendapatan::query()
                    ->where('user_id', auth()->user()->id)
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->sum('bagian_karyawan');
            } else {
                if ($type == 'pendapatan') {
                    // Chart pendapatan untuk admin
                    $bagi_pendapatan = Bagipendapatan::query()
                        ->whereBetween('created_at', [$startOfDay, $endOfDay])
                        ->get();

                    $lihat_harga = $bagi_pendapatan->groupBy('transaksi_id')->map(function ($item) {
                        return $item->first()->transaksi->layanan->harga ?? 0;
                    });

                    $value = $lihat_harga->sum();
                } else {
                    // Chart pengeluaran untuk admin
                    $value = Pengeluaran::query()
                        ->whereBetween('created_at', [$startOfDay, $endOfDay])
                        ->sum('jumlah');
                }
            }

            $chartData[] = $value;
        }

        return $chartData;
    }
}