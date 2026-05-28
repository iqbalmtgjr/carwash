<?php

namespace App\Filament\Pages;

use App\Models\Bagipendapatan;
use App\Models\Payroll;
use App\Models\Pengeluaran;
use App\Models\Transaksi;
use Carbon\Carbon;
use Filament\Pages\Page;

class LaporanKeuangan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.laporan-keuangan';

    public int $bulan;
    public int $tahun;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'owner']);
    }

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function getData(): array
    {
        $start = Carbon::create($this->tahun, $this->bulan, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $totalPendapatan = Transaksi::with('layanan')
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->sum(fn($t) => $t->layanan?->harga ?? 0);

        $totalBagiKaryawan = (int) Bagipendapatan::whereBetween('created_at', [$start, $end])
            ->sum('bagian_karyawan');

        $totalPengeluaran = (int) Pengeluaran::whereBetween('created_at', [$start, $end])
            ->sum('jumlah');

        $totalGaji = (int) Payroll::whereYear('week_start', $this->tahun)
            ->whereMonth('week_start', $this->bulan)
            ->sum('total');

        $bagianOwner     = $totalPendapatan - $totalBagiKaryawan;
        $labaOwner       = $bagianOwner - $totalPengeluaran;
        $jumlahTransaksi = Transaksi::whereBetween('created_at', [$start, $end])->count();

        return compact(
            'totalPendapatan',
            'totalBagiKaryawan',
            'totalPengeluaran',
            'totalGaji',
            'bagianOwner',
            'labaOwner',
            'jumlahTransaksi'
        );
    }
}
