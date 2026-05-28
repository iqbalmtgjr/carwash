<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->date('week_start');
            $table->date('week_end');
            $table->unsignedInteger('total_share')->default(0);
            $table->unsignedInteger('base_salary')->default(0);
            $table->unsignedInteger('attendance_deduction')->default(0);
            $table->unsignedInteger('bonus')->default(0);
            $table->integer('total')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
