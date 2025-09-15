<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\FuelReportController;
use App\Http\Controllers\VehicleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/secretarias', [SecretariatController::class, 'index'])->name('secretariats.index');
    Route::get('/vehicles/status', [VehicleStatusController::class, 'index'])->name('vehicles.status');
    Route::get('/reports/fuel-analysis', [FuelReportController::class, 'index'])->name('reports.fuel-analysis');
    Route::resource('vehicles', VehicleController::class);

});

require __DIR__.'/auth.php';
