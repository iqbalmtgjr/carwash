<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Temukan semua kendaraan tanpa plat yang dipakai oleh lebih dari 1 transaksi
        // Handle dua kondisi: plat NULL (jika migration nullable sudah jalan) atau plat "-" (jika belum)
        $sharedIds = DB::table('transaksi')
            ->join('kendaraan', 'transaksi.kendaraan_id', '=', 'kendaraan.id')
            ->where(function ($q) {
                $q->whereNull('kendaraan.plat')->orWhere('kendaraan.plat', '-');
            })
            ->select('kendaraan.id as kendaraan_id', DB::raw('COUNT(transaksi.id) as total'))
            ->groupBy('kendaraan.id')
            ->having('total', '>', 1)
            ->pluck('kendaraan_id');

        foreach ($sharedIds as $kendaraanId) {
            $original = DB::table('kendaraan')->where('id', $kendaraanId)->first();

            // Ambil semua transaksi yang menunjuk ke kendaraan ini, skip yang pertama
            $transaksis = DB::table('transaksi')
                ->where('kendaraan_id', $kendaraanId)
                ->orderBy('id')
                ->get();

            foreach ($transaksis->skip(1) as $t) {
                // Simpulkan tipe dari nama layanan
                $layanan = DB::table('layanan')->where('id', $t->layanan_id)->first();
                $tipe = 'mobil';
                if ($layanan && str_contains(strtolower($layanan->nama_layanan), 'motor')) {
                    $tipe = 'motor';
                }

                $newId = DB::table('kendaraan')->insertGetId([
                    'tipe'       => $tipe,
                    'merk'       => '',
                    'plat'       => null,
                    'no_wa'      => $original->no_wa ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('transaksi')->where('id', $t->id)->update(['kendaraan_id' => $newId]);
            }

            // Update tipe kendaraan pertama (yang dipertahankan) juga
            $first = $transaksis->first();
            if ($first) {
                $layanan = DB::table('layanan')->where('id', $first->layanan_id)->first();
                $tipe = 'mobil';
                if ($layanan && str_contains(strtolower($layanan->nama_layanan), 'motor')) {
                    $tipe = 'motor';
                }
                DB::table('kendaraan')->where('id', $kendaraanId)->update([
                    'tipe' => $tipe,
                    'merk' => '',
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak bisa di-rollback secara akurat
    }
};
