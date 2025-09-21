<?php

// Importações dos Controllers
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin; // Agrupando controllers do admin
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\{
    AuditLogController,
    DashboardController,
    DiarioBordoController,
    FuelReportController,
    PdfTemplateController,
    ProfileController,
    SearchController,
    SecretariatController,
    VehicleCategoryController,
    VehicleController,
    VehicleStatusController
};

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação e Dashboard
|--------------------------------------------------------------------------
| Rotas que exigem que o usuário esteja logado.
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rotas para Usuários Autenticados
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Grupo de rotas para o Perfil do Usuário
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // Grupo de rotas para o Diário de Bordo
    Route::controller(DiarioBordoController::class)->prefix('diario-de-bordo')->name('diario.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/selecionar-veiculo', 'showSelectVehicle')->name('selectVehicle');
        Route::get('/{vehicle}/checklist', 'showChecklist')->name('checklist');
        Route::post('/{vehicle}/store-checklist', 'storeChecklistAndCreateRun')->name('storeChecklist');
        Route::get('/{run}/iniciar', 'showStartRunForm')->name('showStartRunForm');
        Route::patch('/{run}/iniciar', 'startRun')->name('startRun');
        Route::get('/{run}/finalizar', 'showFinishRun')->name('finishRun');
        Route::patch('/{run}/finalizar', 'updateRun')->name('updateRun');
    });

    // Grupo de rotas para Relatórios
    Route::prefix('reports')->name('reports.')->group(function() {
        Route::get('/fuel-analysis', [FuelReportController::class, 'index'])->name('fuel-analysis');
    });

    // Grupo de rotas para Modelos de PDF
    Route::resource('pdf-templates', PdfTemplateController::class);
    Route::get('pdf-templates/{pdfTemplate}/preview', [PdfTemplateController::class, 'preview'])->name('pdf-templates.preview');
    Route::post('pdf-templates/ajax-preview', [PdfTemplateController::class, 'ajaxPreview'])->name('pdf-templates.ajax-preview');

    // Rotas de Recursos e Cadastros Gerais
    Route::get('/secretarias', [SecretariatController::class, 'index'])->name('secretariats.index');
    Route::get('/vehicles/status', [VehicleStatusController::class, 'index'])->name('vehicles.status');
    Route::resource('vehicles', VehicleController::class);
    Route::resource('vehicle-categories', VehicleCategoryController::class);

    // Outras rotas
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/search', [SearchController::class, 'search'])->name('ajax.search');
});

/*
|--------------------------------------------------------------------------
| Rotas Administrativas (Acesso Restrito)
|--------------------------------------------------------------------------
| Apenas usuários com a permissão 'admin' podem acessar.
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', Admin\UserController::class);
    Route::patch('users/{user}/send-reset-link', [Admin\UserController::class, 'sendPasswordResetLink'])->name('users.send-reset-link');

    Route::get('audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::resource('default-passwords', Admin\DefaultPasswordController::class)->except(['show']);

    Route::get('backups', [Admin\UserDataBackupController::class, 'index'])->name('backups.index');
    Route::get('backups/{backup}/download', [Admin\UserDataBackupController::class, 'download'])->name('backups.download');
});


// Inclui as rotas de autenticação (login, logout, etc.)
require __DIR__.'/auth.php';
