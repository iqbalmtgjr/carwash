<?php

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

use Illuminate\Support\Facades\Artisan;

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

Route::get('/tgl', function () {
    return date('Y-m-d');
});
