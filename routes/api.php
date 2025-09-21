<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiarioBordoController;

// Esta rota será acessível via /api/vehicles/search
// e estará protegida pelo middleware 'auth:sanctum'
Route::middleware('auth:sanctum')->get('/vehicles/search', [DiarioBordoController::class, 'searchVehicles']);
