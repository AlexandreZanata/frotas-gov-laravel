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
    VehicleStatusController,
    OilMaintenanceController,
    OilChangeLogController,
    OilProductController,
    TireController,
    FineController,
    FuelPriceSurveyController,
    VehicleBlockController
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
Route::middleware(['auth','ack.fines'])->group(function () {

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

    // Rota de busca de veículos (JSON) usada pela página de seleção
    Route::get('/api/vehicles/search', [DiarioBordoController::class, 'searchVehicles'])->name('api.vehicles.search');

    // Grupo de rotas para Relatórios
    Route::prefix('reports')->name('reports.')->group(function() {
        Route::get('/fuel-analysis', [FuelReportController::class, 'index'])->name('fuel-analysis');
        Route::get('/fuel', [\App\Http\Controllers\FuelConsumptionReportController::class,'index'])->name('fuel.index');
        Route::post('/fuel/pdf', [\App\Http\Controllers\FuelConsumptionReportController::class,'pdf'])->name('fuel.pdf');
    });

    // Grupo de rotas para Modelos de PDF
    Route::resource('pdf-templates', PdfTemplateController::class);
    Route::get('pdf-templates/{pdfTemplate}/preview', [PdfTemplateController::class, 'preview'])->name('pdf-templates.preview');
    Route::post('pdf-templates/ajax-preview', [PdfTemplateController::class, 'ajaxPreview'])->name('pdf-templates.ajax-preview');

    // Rotas de Recursos e Cadastros Gerais
    Route::get('/secretarias', [SecretariatController::class, 'index'])->name('secretariats.index');
    Route::get('/vehicles/status', [VehicleStatusController::class, 'index'])->name('vehicles.status');
    // Mover rota de bloqueio ANTES do resource para não conflitar com /vehicles/{vehicle}
    Route::get('/vehicles/blocking', [VehicleBlockController::class,'index'])->name('vehicles.blocking');
    Route::get('/api/vehicles/blocking/search', [VehicleBlockController::class,'search'])->name('api.vehicles.blocking.search');
    Route::post('/api/vehicles/{vehicle}/block', [VehicleBlockController::class,'block'])->name('api.vehicles.block');
    Route::post('/api/vehicles/{vehicle}/unblock', [VehicleBlockController::class,'unblock'])->name('api.vehicles.unblock');
    Route::resource('vehicles', VehicleController::class);
    Route::resource('vehicle-categories', VehicleCategoryController::class);

    // Rotas de Manutenção de Óleo / Dashboard
    Route::prefix('manutencao/oleo')->name('oil.')->group(function() {
        Route::get('/', [OilMaintenanceController::class, 'index'])->name('maintenance');
        Route::get('/historico', [OilMaintenanceController::class, 'logs'])->name('logs');
        Route::get('/veiculos/{vehicle}/logs', [OilMaintenanceController::class, 'vehicleLogs'])->name('vehicle.logs');
        Route::post('/registros', [OilChangeLogController::class, 'store'])->name('logs.store');
    });

    // Rotas de Produtos de Óleo
    Route::resource('oil-products', OilProductController::class)->except(['show']);
    Route::get('oil-products/{oilProduct}/history', [OilProductController::class,'history'])->name('oil-products.history');
    Route::post('oil-products/{oilProduct}/adjustments', [\App\Http\Controllers\OilStockAdjustmentController::class,'store'])->name('oil-products.adjustments.store');
    Route::get('oil-products/{oilProduct}/history/export', [OilProductController::class,'exportHistoryCsv'])->name('oil-products.history.export');

    // Módulo de Pneus
    Route::prefix('pneus')->name('tires.')->group(function() {
        Route::get('/dashboard', [TireController::class,'dashboard'])->name('dashboard');
        Route::get('/atencao', [TireController::class,'attention'])->name('attention');
        Route::get('/veiculos/{vehicle}/layout', [\App\Http\Controllers\TireActionController::class,'layout'])->name('vehicle.layout');
        Route::post('/veiculos/{vehicle}/rotacao-interna', [\App\Http\Controllers\TireActionController::class,'internalRotation'])->name('vehicle.rotation.internal');
        Route::post('/veiculos/{vehicle}/rotacao-externa/saida', [\App\Http\Controllers\TireActionController::class,'externalOut'])->name('vehicle.rotation.external.out');
        Route::post('/veiculos/{vehicle}/rotacao-externa/entrada', [\App\Http\Controllers\TireActionController::class,'externalIn'])->name('vehicle.rotation.external.in');
        Route::post('/veiculos/{vehicle}/substituicao', [\App\Http\Controllers\TireActionController::class,'replacement'])->name('vehicle.replacement');
        // Busca de pneus em estoque (multi-campos)
        Route::get('/buscar-estoque', [TireController::class,'searchStock'])->name('search-stock');
        // Recapagem
        Route::post('/{tire}/recapagem/enviar', [TireController::class,'sendForRetread'])->name('retread.send');
        Route::post('/{tire}/recapagem/receber', [TireController::class,'receiveFromRetread'])->name('retread.receive');
    });
    Route::resource('tires', TireController::class)->except(['show']);
    Route::resource('tire-layouts', \App\Http\Controllers\TireLayoutController::class)->except(['show']);

    // Rotas de Multas (Módulo)
    Route::prefix('fines')->name('fines.')->controller(FineController::class)->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/pending/acknowledgement', 'pending')->name('pending');
        // Rotas AJAX de busca em tempo real (antes de rotas com {fine})
        Route::get('/search/vehicles','searchVehicles')->name('search.vehicles');
        Route::get('/search/drivers','searchDrivers')->name('search.drivers');
        Route::get('/search/infraction-codes','searchInfractionCodes')->name('search.infraction-codes');
        Route::get('/search/auto-numbers','searchAutoNumbers')->name('search.auto-numbers');
        // Rotas dependentes de recurso específico
        Route::get('/{fine}', 'show')->name('show');
        Route::get('/{fine}/edit', 'edit')->name('edit');
        Route::put('/{fine}', 'update')->name('update');
        Route::delete('/{fine}', 'destroy')->name('destroy');
        Route::post('/{fine}/infractions', 'storeInfraction')->name('infractions.store');
        Route::patch('/{fine}/infractions/{infraction}', 'updateInfraction')->name('infractions.update');
        Route::delete('/{fine}/infractions/{infraction}', 'deleteInfraction')->name('infractions.destroy');
        Route::post('/{fine}/infractions/{infraction}/attachments', 'uploadAttachment')->name('infractions.attachments.store');
        Route::delete('/attachments/{attachment}', 'deleteAttachment')->name('attachments.destroy');
        Route::post('/{fine}/status', 'changeStatus')->name('change-status');
        Route::post('/{fine}/ack', 'acknowledge')->name('ack');
        Route::get('/{fine}/pdf', 'pdf')->name('pdf');
    });

    // Rotas de Cotação de Combustível (limitado)
    Route::prefix('fuel-surveys')->name('fuel-surveys.')->group(function(){
        Route::get('/', [FuelPriceSurveyController::class,'index'])->name('index');
        Route::get('/create', [FuelPriceSurveyController::class,'create'])->name('create');
        Route::post('/', [FuelPriceSurveyController::class,'store'])->name('store');
        Route::get('/{fuelSurvey}', [FuelPriceSurveyController::class,'show'])->name('show');
    });


    // Outras rotas
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/search', [SearchController::class, 'search'])->name('ajax.search');

    // Módulo de Chat
    Route::get('/chat', [\App\Http\Controllers\ChatController::class,'index'])->name('chat.index');
    Route::get('/api/chat/conversations', [\App\Http\Controllers\ChatController::class,'list'])->name('api.chat.conversations');
    Route::post('/api/chat/conversations', [\App\Http\Controllers\ChatController::class,'store'])->name('api.chat.conversations.store');
    Route::post('/api/chat/conversations/{conversation}/invite', [\App\Http\Controllers\ChatController::class,'invite'])->name('api.chat.conversations.invite');
    Route::post('/api/chat/conversations/{conversation}/leave', [\App\Http\Controllers\ChatController::class,'leave'])->name('api.chat.conversations.leave');
    Route::get('/api/chat/conversations/{conversation}/messages', [\App\Http\Controllers\ChatController::class,'messages'])->name('api.chat.conversations.messages');
    Route::post('/api/chat/messages', [\App\Http\Controllers\ChatController::class,'send'])->name('api.chat.messages.send');
    Route::post('/api/chat/messages/{message}/read', [\App\Http\Controllers\ChatController::class,'read'])->name('api.chat.messages.read');
    Route::get('/api/chat/users/search', [\App\Http\Controllers\ChatController::class,'searchUsers'])->name('api.chat.users.search');
    Route::get('/api/chat/conversations/{conversation}/updates', [\App\Http\Controllers\ChatController::class,'updates'])->name('api.chat.conversations.updates');
    // Rotas adicionais do chat
    Route::get('/api/chat/users/all', [\App\Http\Controllers\ChatController::class,'allUsers'])->name('api.chat.users.all');
    Route::post('/api/chat/direct/{user}', [\App\Http\Controllers\ChatController::class,'direct'])->name('api.chat.direct');
    Route::post('/api/chat/conversations/{conversation}/typing', [\App\Http\Controllers\ChatController::class,'typing'])->name('api.chat.conversations.typing');
    // Templates
    Route::get('/api/chat/templates', [\App\Http\Controllers\ChatController::class,'templates'])->name('api.chat.templates');
    Route::post('/api/chat/templates', [\App\Http\Controllers\ChatController::class,'templateStore'])->name('api.chat.templates.store');
    Route::put('/api/chat/templates/{template}', [\App\Http\Controllers\ChatController::class,'templateUpdate'])->name('api.chat.templates.update');
    Route::delete('/api/chat/templates/{template}', [\App\Http\Controllers\ChatController::class,'templateDelete'])->name('api.chat.templates.delete');
    Route::get('/api/chat/unread/summary', [\App\Http\Controllers\ChatController::class,'unreadSummary'])->name('api.chat.unread.summary');

    // Mensagens automáticas (broadcast)
    Route::get('/chat/broadcast', [\App\Http\Controllers\ChatBroadcastController::class,'index'])->name('chat.broadcast');
    Route::post('/api/chat/broadcast/send', [\App\Http\Controllers\ChatBroadcastController::class,'send'])->name('api.chat.broadcast.send');
    Route::get('/api/chat/broadcast/search-users', [\App\Http\Controllers\ChatBroadcastController::class,'searchUsers'])->name('api.chat.broadcast.search-users');
});

// Rotas Públicas de Verificação de Autenticidade de Multa
Route::get('/verificar-multa', [FineController::class,'verifyForm'])->name('fines.verify.form');
Route::post('/verificar-multa', [FineController::class,'verifySubmit'])->name('fines.verify.submit');

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
