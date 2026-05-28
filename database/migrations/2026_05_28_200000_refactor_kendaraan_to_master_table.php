<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kendaraan_id ke transaksi (nullable dulu)
        Schema::table('transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('kendaraan_id')->nullable()->after('layanan_id');
        });

        // 2. Deduplikasi: per plat, simpan kendaraan dengan id terbesar (terbaru)
        $platGroups = DB::table('kendaraan')
            ->select('plat', DB::raw('MAX(id) as keep_id'))
            ->groupBy('plat')
            ->get();

        foreach ($platGroups as $group) {
            // Kumpulkan semua transaksi_id yang terkait plat ini
            $transaksiIds = DB::table('kendaraan')
                ->where('plat', $group->plat)
                ->pluck('transaksi_id');

            // Arahkan semua transaksi tersebut ke satu kendaraan master
            DB::table('transaksi')
                ->whereIn('id', $transaksiIds)
                ->update(['kendaraan_id' => $group->keep_id]);

            // Hapus duplikat (semua kecuali yang dipilih)
            DB::table('kendaraan')
                ->where('plat', $group->plat)
                ->where('id', '!=', $group->keep_id)
                ->delete();
        }

        // 3. Hapus transaksi_id dari kendaraan, tambah unique pada plat
        Schema::table('kendaraan', function (Blueprint $table) {
            $table->dropForeign(['transaksi_id']);
            $table->dropColumn('transaksi_id');
            $table->unique('plat');
        });

        // 4. Jadikan kendaraan_id NOT NULL + tambah FK
        Schema::table('transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('kendaraan_id')->nullable(false)->change();
            $table->foreign('kendaraan_id')->references('id')->on('kendaraan')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        // Kembalikan transaksi_id ke kendaraan
        Schema::table('kendaraan', function (Blueprint $table) {
            $table->dropUnique(['plat']);
            $table->unsignedBigInteger('transaksi_id')->nullable()->after('id');
        });

        // Isi ulang transaksi_id dari relasi yang masih ada
        $transaksis = DB::table('transaksi')->whereNotNull('kendaraan_id')->get();
        foreach ($transaksis as $t) {
            DB::table('kendaraan')
                ->where('id', $t->kendaraan_id)
                ->update(['transaksi_id' => $t->id]);
        }

        // Tambah FK transaksi_id kembali
        Schema::table('kendaraan', function (Blueprint $table) {
            $table->foreign('transaksi_id')->references('id')->on('transaksi')->onDelete('cascade');
        });

        // Hapus kendaraan_id dari transaksi
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['kendaraan_id']);
            $table->dropColumn('kendaraan_id');
        });
    }
};
