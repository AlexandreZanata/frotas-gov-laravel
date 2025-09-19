<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\FuelReportController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleCategoryController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DiarioBordoController;
use App\Http\Controllers\SecretariatController; // Adicionado para corrigir possível falta

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
    Route::resource('vehicle-categories', VehicleCategoryController::class);
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    // Adicione esta rota, de preferência dentro do grupo de middleware 'auth'
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('ajax.search');

    // ===================================================================
    // GRUPO DE ROTAS DO DIÁRIO DE BORDO
    // ===================================================================
    Route::prefix('diario-de-bordo')->name('diario.')->group(function () {
        // Ponto de entrada que redireciona para a etapa correta
        Route::get('/', [DiarioBordoController::class, 'index'])->name('index');

        // Etapa 1: Página para selecionar o veículo
        Route::get('/selecionar-veiculo', [DiarioBordoController::class, 'showSelectVehicle'])->name('selectVehicle');

        // Etapa 2: Página do Checklist
        Route::get('/{vehicle}/checklist', [DiarioBordoController::class, 'showChecklist'])->name('checklist');

        // Processa o Checklist, CRIA a corrida "pendente" e redireciona para a próxima etapa
        Route::post('/{vehicle}/store-checklist', [DiarioBordoController::class, 'storeChecklistAndCreateRun'])->name('storeChecklist');

        // Etapa 3: Página para inserir KM e Destino (recebe a corrida já criada)
        Route::get('/{run}/iniciar', [DiarioBordoController::class, 'showStartRunForm'])->name('showStartRunForm');

        // ATUALIZA a corrida com os dados de início e a torna "ativa"
        Route::patch('/{run}/iniciar', [DiarioBordoController::class, 'startRun'])->name('startRun');

        // Etapa 4: Página para finalizar a corrida
        Route::get('/{run}/finalizar', [DiarioBordoController::class, 'showFinishRun'])->name('finishRun');

        // Processa a finalização da corrida
        Route::patch('/{run}/finalizar', [DiarioBordoController::class, 'updateRun'])->name('updateRun');
    });

    // Rota para a API de busca de veículos (permanece igual)
    Route::get('/api/vehicles/search', [DiarioBordoController::class, 'searchVehicles'])->name('api.vehicles.search');
});

/**
 * ===================================================================
 * ROTAS ADMINISTRATIVAS (Acesso restrito a role_id = 1)
 * ===================================================================
 */
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

    // --- Gerenciamento de Usuários ---
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::patch('users/{user}/send-reset-link', [\App\Http\Controllers\Admin\UserController::class, 'sendPasswordResetLink'])->name('users.send-reset-link');

    // --- Histórico de Alterações (Auditoria) ---
    Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

    // --- Senhas Padrão ---
    Route::resource('default-passwords', \App\Http\Controllers\Admin\DefaultPasswordController::class)->except(['show']);

    // --- Backups de Usuários Excluídos ---
    Route::get('backups', [\App\Http\Controllers\Admin\UserDataBackupController::class, 'index'])->name('backups.index');
    Route::get('backups/{backup}/download', [\App\Http\Controllers\Admin\UserDataBackupController::class, 'download'])->name('backups.download');

});




require __DIR__.'/auth.php';
