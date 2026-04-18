<?php

namespace App\Filament\Widgets;

use App\Models\Kasbon;
use App\Models\Bagipendapatan;
use App\Models\Pengeluaran;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\BagipendapatanResource\Pages\ListBagipendapatans;
use Carbon\Carbon;

class PembagianUpah extends BaseWidget
{
    use InteractsWithPageTable;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListBagipendapatans::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();
        $filteredIds = $query->pluck('id');

        if ($filteredIds->isEmpty()) {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();

            // ✅ FIX KRITIS #2: Tambah eager loading
            $bagi_pendapatan = Bagipendapatan::query()
                ->whereBetween('created_at', [$start, $end])
                ->with(['transaksi.layanan', 'user'])
                ->get();
        } else {
            // ✅ FIX KRITIS #2: Tambah eager loading
            $bagi_pendapatan = Bagipendapatan::query()
                ->whereIn('id', $filteredIds)
                ->with(['transaksi.layanan', 'user'])
                ->get();

            $start = $bagi_pendapatan->min('created_at');
            $end = $bagi_pendapatan->max('created_at');

            if (!$start || !$end) {
                $start = now()->startOfDay();
                $end = now()->endOfDay();
            } else {
                $start = Carbon::parse($start)->startOfDay();
                $end = Carbon::parse($end)->endOfDay();
            }
        }

        $stats = [];

        if (auth()->user()->role == 'user') {
            $bagi_pendapatan_user = $bagi_pendapatan->where('user_id', auth()->user()->id);

            $total_pendapatan = $bagi_pendapatan_user->sum('bagian_karyawan');

            $kasbon = Kasbon::query()
                ->where('user_id', auth()->user()->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('nominal');

            $total_bersih = $total_pendapatan - $kasbon;

            $stats[] = Stat::make('Pendapatan Saya', 'Rp. ' . number_format($total_pendapatan, 0, ',', '.'))
                ->color('success')
                ->description('Total bagian karyawan')
                ->descriptionIcon('heroicon-m-banknotes');

            $stats[] = Stat::make('Kasbon Saya', 'Rp. ' . number_format($kasbon, 0, ',', '.'))
                ->color('warning')
                ->description('Total kasbon periode ini')
                ->descriptionIcon('heroicon-m-credit-card');

            $stats[] = Stat::make('Pendapatan Bersih', 'Rp. ' . number_format($total_bersih, 0, ',', '.'))
                ->color($total_bersih >= 0 ? 'success' : 'danger')
                ->description($total_bersih >= 0 ? 'Pendapatan - Kasbon' : 'Minus (Utang Kasbon)')
                ->descriptionIcon($total_bersih >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle');
        } else {
            $pengeluaran = Pengeluaran::whereBetween('created_at', [$start, $end])
                ->sum('jumlah');

            $total_gaji_karyawan = $bagi_pendapatan->sum('bagian_karyawan');

            $lihat_harga = $bagi_pendapatan->groupBy('transaksi_id')->map(function ($item) {
                return [
                    'transaksi_id' => $item->first()->transaksi_id,
                    'layanan' => $item->first()->transaksi->layanan->nama_layanan,
                    'harga' => $item->first()->transaksi->layanan->harga,
                ];
            });

            $pemasukan_kotor = $lihat_harga->sum('harga');
            $pemasukan_bersih = $pemasukan_kotor - $pengeluaran - $total_gaji_karyawan;
            $pendapatan_ee = $pemasukan_bersih * 0.5;

            $kasbon = Kasbon::query()
                ->whereBetween('created_at', [$start, $end])
                ->sum('nominal');

            $uang_ditangan = $pemasukan_kotor - $pengeluaran - $kasbon;

            if (auth()->user()->role == 'admin' && auth()->user()->id == 1) {
                $stats[] = Stat::make('Pemasukan Bersih Owner', 'Rp. ' . number_format($pemasukan_bersih, 0, ',', '.'))
                    ->color('success')
                    ->description('Setelah dikurangi gaji & pengeluaran')
                    ->descriptionIcon('heroicon-m-banknotes');

                $stats[] = Stat::make('Uang di Tangan', 'Rp. ' . number_format($uang_ditangan, 0, ',', '.'))
                    ->color('info')
                    ->description('Pemasukan kotor - pengeluaran - kasbon')
                    ->descriptionIcon('heroicon-m-currency-dollar');

                $stats[] = Stat::make('Total Gaji Karyawan', 'Rp. ' . number_format($total_gaji_karyawan - $kasbon, 0, ',', '.'))
                    ->color('success')
                    ->description('Total bagian karyawan: Rp. ' . number_format($total_gaji_karyawan, 0, ',', '.'))
                    ->descriptionIcon('heroicon-m-users');
            }

            $stats[] = Stat::make('Kasbon', 'Rp. ' . number_format($kasbon, 0, ',', '.'))
                ->color('warning')
                ->description('Total kasbon periode ini')
                ->descriptionIcon('heroicon-m-credit-card');

            // ✅ FIX KRITIS #1: Ambil semua kasbon sekaligus, hindari N+1 query
            $userIds = $bagi_pendapatan->pluck('user_id')->unique();

            $all_kasbons = Kasbon::query()
                ->whereIn('user_id', $userIds)
                ->whereBetween('created_at', [$start, $end])
                ->get()
                ->groupBy('user_id')
                ->map(fn($k) => $k->sum('nominal'));

            $breakdown_karyawan = $bagi_pendapatan
                ->groupBy('user_id')
                ->map(function ($group) use ($all_kasbons) {
                    $userId = $group->first()->user_id;
                    $kasbon = $all_kasbons->get($userId, 0);

                    return [
                        'user' => $group->first()->user->name,
                        'total' => $group->sum('bagian_karyawan') - $kasbon,
                        'bagian_karyawan' => $group->sum('bagian_karyawan'),
                        'kasbon' => $kasbon,
                    ];
                })
                ->sortByDesc('total')
                ->values();

            foreach ($breakdown_karyawan as $pembagian) {
                $color = 'success';
                $description = 'Bagian: Rp. ' . number_format($pembagian['bagian_karyawan'], 0, ',', '.');

                if ($pembagian['kasbon'] > 0) {
                    $description .= ' | Kasbon: Rp. ' . number_format($pembagian['kasbon'], 0, ',', '.');
                }

                if ($pembagian['total'] < 0) {
                    $color = 'danger';
                    $description .= ' (Minus)';
                }

                $stats[] = Stat::make($pembagian['user'], 'Rp. ' . number_format($pembagian['total'], 0, ',', '.'))
                    ->color($color)
                    ->description($description)
                    ->descriptionIcon('heroicon-m-user');
            }
        }

        if (empty($stats)) {
            $stats[] = Stat::make('Tidak Ada Data', 'Rp. 0')
                ->color('warning')
                ->description('Tidak ada transaksi pada periode ini')
                ->descriptionIcon('heroicon-m-exclamation-triangle');
        }

        return $stats;
    }
}
