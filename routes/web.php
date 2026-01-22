<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PublicReportController;

// Controladores del Panel Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\IncidentController;
use App\Http\Controllers\Admin\IncidentConfigController;
use App\Http\Controllers\Admin\SiaCodeController;
use App\Http\Controllers\Admin\AlarmZoneController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ServicePlanController;
use App\Http\Controllers\Admin\GpsDeviceController; // <--- NUEVO

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS / PANTALLAS DE VISUALIZACIÓN
|--------------------------------------------------------------------------
*/

Route::get('/', function () { return view('welcome'); });

// Centro de Monitoreo (Video Wall)
Route::get('/monitor', [MonitoringController::class, 'index'])->name('monitor.index');
Route::get('/mapa', [MonitoringController::class, 'map'])->name('monitor.map');

// API Interna (Para actualización en tiempo real vía AJAX)
Route::get('/api/live-events', [MonitoringController::class, 'getLiveEvents'])->name('api.live-events');

// --- VERIFICACIÓN PÚBLICA DE DOCUMENTOS (QR) ---
// Esta ruta es pública pero protegida por firma criptográfica (signed)
Route::get('/verify/report/{id}', [PublicReportController::class, 'verify'])
    ->name('report.verify')
    ->middleware('signed');


/*
|--------------------------------------------------------------------------
| PANEL ADMINISTRATIVO Y OPERATIVO (Requiere Login)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // 1. DASHBOARD PRINCIPAL
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. GESTIÓN DE CLIENTES (CRM)
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    
    // Contactos
    Route::post('customers/{id}/contacts', [CustomerController::class, 'storeContact'])->name('customers.contacts.store');
    Route::put('contacts/{id}', [CustomerController::class, 'updateContact'])->name('contacts.update');
    Route::delete('contacts/{id}', [CustomerController::class, 'destroyContact'])->name('contacts.destroy');


    // 3. GESTIÓN DE CUENTAS DE ALARMA (PANELES)
    Route::resource('accounts', AccountController::class); 

    // Notas y Bitácora
    Route::put('accounts/{id}/notes', [AccountController::class, 'updateNotes'])->name('accounts.notes.update');
    Route::post('accounts/{id}/log', [AccountController::class, 'storeLog'])->name('accounts.log.store');

    // Sub-módulos
    Route::post('accounts/{id}/partitions', [AccountController::class, 'storePartition'])->name('accounts.partitions.store');
    Route::put('partitions/{id}', [AccountController::class, 'updatePartition'])->name('partitions.update');
    Route::delete('partitions/{id}', [AccountController::class, 'destroyPartition'])->name('partitions.destroy');

    Route::post('accounts/{id}/users', [AccountController::class, 'storePanelUser'])->name('accounts.users.store');
    Route::put('panel-users/{id}', [AccountController::class, 'updatePanelUser'])->name('accounts.users.update');
    Route::delete('panel-users/{id}', [AccountController::class, 'destroyPanelUser'])->name('accounts.users.destroy');

    Route::post('accounts/{id}/schedules/temp', [AccountController::class, 'storeTempSchedule'])->name('accounts.schedules.temp.store');
    Route::post('accounts/{id}/schedules/weekly', [AccountController::class, 'storeWeeklySchedule'])->name('accounts.schedules.weekly.store');
    Route::delete('schedules/{id}', [AccountController::class, 'destroySchedule'])->name('schedules.destroy');

    Route::post('accounts/{id}/zones', [AlarmZoneController::class, 'store'])->name('accounts.zones.store');
    Route::put('zones/{id}', [AlarmZoneController::class, 'update'])->name('zones.update');
    Route::delete('zones/{id}', [AlarmZoneController::class, 'destroy'])->name('zones.destroy');


    // 4. MÓDULO DE OPERACIONES
    Route::prefix('operations')->group(function () {
        Route::get('/', [IncidentController::class, 'console'])->name('operations.console');
        Route::post('/take/{id}', [IncidentController::class, 'take'])->name('incidents.take');
        Route::post('/manual-event', [IncidentController::class, 'storeManual'])->name('incidents.manual');
        Route::get('/incident/{id}', [IncidentController::class, 'manage'])->name('operations.manage');
        Route::post('/incident/{id}/hold', [IncidentController::class, 'hold'])->name('incidents.hold');
        Route::post('/incident/{id}/close', [IncidentController::class, 'close'])->name('incidents.close');
        Route::post('/incident/{id}/note', [IncidentController::class, 'addNote'])->name('incidents.add-note');
    });


    // 5. CONFIGURACIÓN DEL SISTEMA
    Route::resource('sia-codes', SiaCodeController::class);
    Route::resource('users', UserController::class);

    Route::prefix('config')->name('config.')->group(function () {
        Route::get('resolutions', [IncidentConfigController::class, 'indexResolutions'])->name('resolutions.index');
        Route::post('resolutions', [IncidentConfigController::class, 'storeResolution'])->name('resolutions.store');
        Route::delete('resolutions/{id}', [IncidentConfigController::class, 'destroyResolution'])->name('resolutions.destroy');
        
        Route::get('hold-reasons', [IncidentConfigController::class, 'indexHoldReasons'])->name('hold-reasons.index');
        Route::post('hold-reasons', [IncidentConfigController::class, 'storeHoldReason'])->name('hold-reasons.store');
        Route::delete('hold-reasons/{id}', [IncidentConfigController::class, 'destroyHoldReason'])->name('hold-reasons.destroy');

        Route::resource('plans', ServicePlanController::class)->except(['create', 'edit', 'show']);
    });

    // 6. MÓDULO DE REPORTES (Inteligencia)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        // Reporte Listado (Tablas)
        Route::get('/list', [ReportController::class, 'printList'])->name('list');
        // Reporte Gráfico (Resumen)
        Route::get('/summary', [ReportController::class, 'printSummary'])->name('summary');
        // Detalle Forense
        Route::get('/detail/{id}', [ReportController::class, 'detail'])->name('detail');
    });

    // 7. MÓDULO DE RASTREO GPS Y FLOTAS (NUEVO)
    // ----------------------------------------------------
    Route::prefix('gps')->name('gps.')->group(function () {
        // Dispositivos (Inventario y Estado)
        Route::resource('devices', GpsDeviceController::class);
        
        // Comandos Remotos (AJAX)
        Route::post('devices/{id}/command', [GpsDeviceController::class, 'sendCommand'])->name('devices.command');

        // Gestión de Flotas (Reportes, Rutas, Conductores) - Próximamente
        Route::get('/fleet', function() { return "Módulo de Flotas en construcción"; })->name('fleet.index');
    });

});

require __DIR__.'/auth.php';