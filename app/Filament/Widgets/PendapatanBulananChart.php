<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use App\Models\Bagipendapatan;
use App\Models\Pengeluaran;
use Filament\Widgets\ChartWidget;

class PendapatanBulananChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendapatan Per Bulan';
    protected static ?int $sort = 3;
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
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            if (auth()->user()->role != 'admin') {
                $total_pendapatan = Bagipendapatan::where('user_id', auth()->user()->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('bagian_karyawan');
            } else {
                $pendapatan_kotor = Transaksi::with('layanan')
                    ->whereBetween('created_at', [$start, $end])
                    ->get()
                    ->sum(fn(Transaksi $transaksi): int => $transaksi->layanan->harga ?? 0);

                $pengeluaran = Pengeluaran::whereBetween('created_at', [$start, $end])
                    ->sum('jumlah');

                $total_pendapatan = $pendapatan_kotor - $pengeluaran;
            }

            $data[] = $total_pendapatan;
            $labels[] = $month->format('M Y');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
