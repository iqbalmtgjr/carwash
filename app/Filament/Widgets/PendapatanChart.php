<?php

namespace App\Filament\Widgets;

use App\Models\Kendaraan;
use App\Models\Transaksi;
use App\Models\Bagipendapatan;
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
        $now = now();
        $start = $now->copy()->startOfWeek();
        $end = $now->copy()->endOfWeek();
        
        $data = [];
        for ($i = 1; $i <= 30; $i++) {
            if(auth()->user()->role != 'admin'){
                $total_pendapatan = Bagipendapatan::where('user_id', auth()->user()->id)
                                    ->whereBetween('created_at', [now()->startOfMonth()->addDays($i - 1)->startOfDay(), now()->startOfMonth()->addDays($i - 1)->endOfDay()])
                                    ->sum('bagian_karyawan');
            }else{
                $total_pendapatan = Transaksi::whereBetween('created_at', [now()->startOfMonth()->addDays($i - 1)->startOfDay(), now()->startOfMonth()->addDays($i - 1)->endOfDay()])->get()->sum(fn(Transaksi $transaksi): int => $transaksi->layanan->harga);
            }
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
