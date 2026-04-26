<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TargetHarian extends BaseWidget
{
    // Auto refresh setiap 30 detik
    protected static ?string $pollingInterval = '30s';

    // Prioritas tampil paling atas di dashboard
    protected static ?int $sort = -1;

    /**
     * Widget hanya untuk admin
     */
    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        $start = now()->copy()->startOfDay();
        $end   = now()->copy()->endOfDay();

        // Ambil transaksi hari ini dengan eager load layanan dan kendaraan
        $transaksi_hari_ini = Transaksi::with(['layanan', 'kendaraan'])
            ->whereBetween('created_at', [$start, $end])
            ->get();

        // Hitung omzet dari harga layanan
        $omzet = $transaksi_hari_ini->sum(fn(Transaksi $t) => $t->layanan->harga ?? 0);

        // Hitung jumlah mobil dan motor dari relasi kendaraan
        $jumlah_mobil = $transaksi_hari_ini
            ->where('kendaraan.tipe', 'mobil')
            ->count();

        $jumlah_motor = $transaksi_hari_ini
            ->where('kendaraan.tipe', 'motor')
            ->count();

        $total_kendaraan = $jumlah_mobil + $jumlah_motor;

        // Target
        $target_omzet = 300_000;
        $target_mobil = 5;

        // Progress target omzet (dibatasi maksimal 100%)
        $progress = $target_omzet > 0
            ? min(100, round(($omzet / $target_omzet) * 100, 1))
            : 0;

        // Sisa target (tidak negatif)
        $sisa = max(0, $target_omzet - $omzet);

        // Status: Aman jika omzet & jumlah mobil tercapai
        $is_aman = $omzet >= $target_omzet && $jumlah_mobil >= $target_mobil;
        $status  = $is_aman ? 'Aman' : 'Belum';
        $status_color = $is_aman ? 'success' : 'warning';

        // Progress mobil
        $progress_mobil = min(100, round(($jumlah_mobil / $target_mobil) * 100, 1));

        return [
            Stat::make('Omzet Hari Ini', 'Rp. ' . number_format($omzet, 0, ',', '.'))
                ->description("{$total_kendaraan} kendaraan ({$jumlah_mobil} mobil, {$jumlah_motor} motor)")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($omzet >= $target_omzet ? 'success' : 'primary'),

            Stat::make('Jumlah Mobil', $jumlah_mobil . ' / ' . $target_mobil)
                ->description('Progress: ' . $progress_mobil . '%')
                ->descriptionIcon('heroicon-m-truck')
                ->color($jumlah_mobil >= $target_mobil ? 'success' : 'warning'),

            Stat::make('Jumlah Motor', (string) $jumlah_motor)
                ->description('Total motor hari ini')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('info'),

            Stat::make('Progress Target', $progress . '%')
                ->description('Target: Rp. ' . number_format($target_omzet, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($progress >= 100 ? 'success' : 'info'),

            Stat::make('Sisa Target', 'Rp. ' . number_format($sisa, 0, ',', '.'))
                ->description('Min. mobil: ' . $target_mobil)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($sisa === 0 ? 'success' : 'warning'),

            Stat::make('Status', $status)
                ->description(
                    $is_aman
                        ? 'Target omzet & jumlah mobil tercapai'
                        : ($omzet < $target_omzet && $jumlah_mobil < $target_mobil
                            ? 'Omzet & jumlah mobil belum mencapai target'
                            : ($omzet < $target_omzet
                                ? 'Omzet belum mencapai target'
                                : 'Jumlah mobil belum mencapai target'
                            )
                        )
                )
                ->descriptionIcon($is_aman ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($status_color),
        ];
    }
}
