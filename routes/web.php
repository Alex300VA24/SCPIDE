<?php

use App\Http\Controllers\ConsultaPdfController;
use App\Http\Controllers\ConsultaPrintController;
use App\Http\Controllers\DashboardController;
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

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('consulta/pdf', ConsultaPdfController::class)->name('consulta.pdf');
    Route::get('consulta/print', ConsultaPrintController::class)->name('consulta.print');
});

require __DIR__.'/auth.php';
