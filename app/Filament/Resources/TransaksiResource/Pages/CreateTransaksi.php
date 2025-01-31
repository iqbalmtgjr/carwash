<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use Filament\Actions;
use App\Models\Transaksi;
use App\Models\Bagipendapatan;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TransaksiResource;
use App\Models\Transaksiuser;

class CreateTransaksi extends CreateRecord
{
    protected static string $resource = TransaksiResource::class;

    protected function afterCreate(): void
    {
        $cek_transaksi = Transaksi::find($this->record->id);
        $get_pembagian = $cek_transaksi->layanan->bagi_karyawan;
        $transaksiuser = Transaksiuser::where('transaksi_id', $this->record->id)->get();
        $bagi_rata = $get_pembagian / $transaksiuser->count();
        foreach ($transaksiuser as $bagi) {
            Bagipendapatan::create([
                'transaksi_id' => $this->record->id,
                'user_id' => $bagi->user_id,
                'bagian_karyawan' => $bagi_rata,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.transaksi.index');
    }
}
