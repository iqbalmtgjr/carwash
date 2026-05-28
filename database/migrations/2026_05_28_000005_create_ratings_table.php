<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->tinyInteger('score')->unsigned(); // 1–5
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->unique('transaksi_id'); // satu transaksi satu rating
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
