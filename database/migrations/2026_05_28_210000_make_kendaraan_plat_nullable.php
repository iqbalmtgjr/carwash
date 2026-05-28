<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kendaraan', function (Blueprint $table) {
            $table->string('plat')->nullable()->change();
        });

        // Konversi semua "-" lama menjadi NULL
        DB::table('kendaraan')->where('plat', '-')->update(['plat' => null]);
    }

    public function down(): void
    {
        DB::table('kendaraan')->whereNull('plat')->update(['plat' => '-']);

        Schema::table('kendaraan', function (Blueprint $table) {
            $table->string('plat')->nullable(false)->change();
        });
    }
};
