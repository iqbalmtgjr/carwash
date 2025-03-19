<?php

namespace App\Filament\Widgets;

use App\Models\Kendaraan;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use Filament\Widgets\ChartWidget;

class PendapatanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendapatan';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];

    public function getColumns(): int | string | array
    {
        return $this->columnSpan;
    }

    protected function getData(): array
    {
        $data = [];
        for ($i = 1; $i <= 30; $i++) {
            $total_pendapatan = Transaksi::whereBetween('created_at', [now()->startOfMonth()->addDays($i - 1)->startOfDay(), now()->startOfMonth()->addDays($i - 1)->endOfDay()])->get()->sum(fn(Transaksi $transaksi): int => $transaksi->layanan->harga);
            $total_pengeluaran = Pengeluaran::whereBetween('created_at', [now()->startOfMonth()->addDays($i - 1)->startOfDay(), now()->startOfMonth()->addDays($i - 1)->endOfDay()])->sum('jumlah');
            $data[] = $total_pendapatan;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan',
                    'data' => $data,
                ],
            ],
            'labels' => array_map(fn($day) => now()->startOfMonth()->addDays($day - 1)->format('d/m/Y'), range(1, now()->endOfMonth()->diffInDays(now()->startOfMonth()) + 1)),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
