<?php

namespace App\Filament\Widgets;

use App\Models\Kendaraan;
use App\Models\Transaksi;
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
            $total_pendapatan = Transaksi::whereDay('transaksi.created_at', $i)
                ->join('layanan', 'transaksi.layanan_id', '=', 'layanan.id')
                ->sum('layanan.harga');
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
