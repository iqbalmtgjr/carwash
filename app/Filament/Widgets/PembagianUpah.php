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
        
        // Tentukan rentang tanggal berdasarkan filter
        if ($filteredIds->isEmpty()) {
            // Default: minggu ini (Senin - Minggu)
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

        $stats = [];

        if (auth()->user()->role == 'user') {
            // ============================================
            // UNTUK USER/KARYAWAN - HANYA LIHAT SENDIRI
            // ============================================
            
            // Filter hanya data user yang login
            $bagi_pendapatan_user = $bagi_pendapatan->where('user_id', auth()->user()->id);
            
            // Total pendapatan user
            $total_pendapatan = $bagi_pendapatan_user->sum('bagian_karyawan');

            // Kasbon user berdasarkan rentang tanggal
            $kasbon = Kasbon::query()
                ->where('user_id', auth()->user()->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('nominal');

            // Total setelah dikurangi kasbon
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
            // ============================================
            // UNTUK ADMIN - LIHAT SEMUA
            // ============================================
            
            // Hitung pengeluaran berdasarkan rentang tanggal
            $pengeluaran = Pengeluaran::whereBetween('created_at', [$start, $end])
                ->sum('jumlah');

            // Hitung total gaji karyawan
            $total_gaji_karyawan = $bagi_pendapatan->sum('bagian_karyawan');

            // Hitung pemasukan kotor
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

            // Hitung kasbon berdasarkan rentang tanggal
            $kasbon = Kasbon::query()
                ->whereBetween('created_at', [$start, $end])
                ->sum('nominal');

            $uang_ditangan = $pemasukan_kotor - $pengeluaran - $kasbon;

            // Stats untuk Admin
            if(auth()->user()->role == 'admin' && auth()->user()->id == 1){
                $stats[] = Stat::make('Pemasukan Bersih Owner', 'Rp. ' . number_format($pemasukan_bersih, 0, ',', '.'))
                    ->color('success')
                    ->description('Setelah dikurangi gaji & pengeluaran')
                    ->descriptionIcon('heroicon-m-banknotes');
    
                $stats[] = Stat::make('Pemasukan Setengah Owner', 'Rp. ' . number_format($pendapatan_ee, 0, ',', '.'))
                    ->color('success')
                    ->description('50% dari pemasukan bersih')
                    ->descriptionIcon('heroicon-m-calculator');
                $stats[] = Stat::make('Uang di Tangan', 'Rp. ' . number_format($uang_ditangan, 0, ',', '.'))
                    ->color('info')
                    ->description('Pemasukan kotor - pengeluaran - kasbon')
                    ->descriptionIcon('heroicon-m-currency-dollar');
                $stats[] = Stat::make('Total Gaji Karyawan', 'Rp. ' . number_format($total_gaji_karyawan, 0, ',', '.'))
                    ->color('success')
                    ->description('Total bagian karyawan')
                    ->descriptionIcon('heroicon-m-users');
            }

            $stats[] = Stat::make('Kasbon', 'Rp. ' . number_format($kasbon, 0, ',', '.'))
                ->color('warning')
                ->description('Total kasbon periode ini')
                ->descriptionIcon('heroicon-m-credit-card');

           

            // Tambahan: Breakdown per karyawan
            $breakdown_karyawan = $bagi_pendapatan
                ->groupBy('user_id')
                ->map(function ($group) use ($start, $end) {
                    $kasbon = Kasbon::query()
                        ->where('user_id', $group->first()->user_id)
                        ->whereBetween('created_at', [$start, $end])
                        ->sum('nominal');

                    return [
                        'user' => $group->first()->user->name,
                        'total' => $group->sum('bagian_karyawan') - $kasbon,
                        'bagian_karyawan' => $group->sum('bagian_karyawan'),
                        'kasbon' => $kasbon,
                    ];
                })
                ->sortByDesc('total')
                ->values();

            // Tambahkan stat untuk setiap karyawan
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

        // Jika tidak ada data, tampilkan pesan
        if (empty($stats)) {
            $stats[] = Stat::make('Tidak Ada Data', 'Rp. 0')
                ->color('warning')
                ->description('Tidak ada transaksi pada periode ini')
                ->descriptionIcon('heroicon-m-exclamation-triangle');
        }

        return $stats;
    }
}