<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');
    return 'Optimization cache cleared!';
});

Route::get('/migrate', function () {
    Artisan::call('migrate');
    return 'Migration completed!';
});

Route::get('/', function () {
    return redirect('admin');
});

// Rating pelanggan
Route::get('/rating/{transaksi}', [App\Http\Controllers\RatingController::class, 'show'])->name('rating.show');
Route::post('/rating/{transaksi}', [App\Http\Controllers\RatingController::class, 'store'])->name('rating.store');

// Absensi — display QR (untuk layar di pintu masuk)
Route::get('/absensi', [App\Http\Controllers\AttendanceController::class, 'display'])->name('absensi.display');

// Absensi — karyawan scan QR lalu buka URL ini
Route::get('/absensi/scan/{token}', [App\Http\Controllers\AttendanceController::class, 'showScan'])->name('absensi.scan');
Route::post('/absensi/scan/{token}', [App\Http\Controllers\AttendanceController::class, 'submitScan'])->name('absensi.submit');
