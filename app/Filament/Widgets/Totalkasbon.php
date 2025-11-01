<?php

namespace App\Filament\Widgets;

use App\Models\Kasbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\KasbonResource\Pages\ListKasbons;
use Carbon\Carbon;

class Totalkasbon extends BaseWidget
{
    use InteractsWithPageTable;

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        if (auth()->user()->role == 'admin') {
            return true;
        }
        return false;
    }

    protected function getTablePage(): string
    {
        return ListKasbons::class;
    }

    protected function getStats(): array
    {
        // Ambil query dari tabel dengan filter yang aktif
        $query = $this->getPageTableQuery();
        
        // Ambil semua data yang terfilter (BUKAN hanya ID)
        $kasbonData = $query->with('user')->get();
        
        // Jika data kosong (tidak ada filter atau filter tidak menghasilkan data)
        if ($kasbonData->isEmpty()) {
            // Fallback ke minggu ini
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            
            $kasbonData = Kasbon::query()
                ->whereBetween('created_at', [$start, $end])
                ->with('user')
                ->get();
        }

        $stats = [];

        // Total kasbon dari data yang terfilter
        $total_kasbon = $kasbonData->sum('nominal');

        // Hitung jumlah karyawan yang kasbon
        $jumlah_karyawan = $kasbonData->groupBy('user_id')->count();

        $stats[] = Stat::make('Total Kasbon', 'Rp. ' . number_format($total_kasbon, 0, ',', '.'))
            ->color('danger')
            ->description($jumlah_karyawan . ' karyawan kasbon')
            ->descriptionIcon('heroicon-m-credit-card');

        // Breakdown per karyawan dari data yang terfilter
        $kasbon_per_karyawan = $kasbonData
            ->groupBy('user_id')
            ->map(function ($group) {
                return [
                    'user' => $group->first()->user->name ?? 'Unknown',
                    'total' => $group->sum('nominal'),
                    'jumlah_kasbon' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Tambahkan stat untuk setiap karyawan
        foreach ($kasbon_per_karyawan as $kasbon) {
            $description = $kasbon['jumlah_kasbon'] . ' kali kasbon';
            
            $stats[] = Stat::make($kasbon['user'], 'Rp. ' . number_format($kasbon['total'], 0, ',', '.'))
                ->color('warning')
                ->description($description)
                ->descriptionIcon('heroicon-m-user');
        }

        // Jika tidak ada data sama sekali, tampilkan pesan
        if ($kasbonData->isEmpty()) {
            $stats = [
                Stat::make('Tidak Ada Data', 'Rp. 0')
                    ->color('success')
                    ->description('Tidak ada kasbon pada periode ini')
                    ->descriptionIcon('heroicon-m-check-circle')
            ];
        }

        return $stats;
    }
}