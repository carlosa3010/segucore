<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;

// Controladores del Panel Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\IncidentController;
use App\Http\Controllers\Admin\IncidentConfigController;
use App\Http\Controllers\Admin\SiaCodeController;
use App\Http\Controllers\Admin\AlarmZoneController;

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
    // Acciones extra de clientes
    Route::post('customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::post('customers/{id}/contacts', [CustomerController::class, 'storeContact'])->name('customers.contacts.store');
    Route::delete('contacts/{id}', [CustomerController::class, 'destroyContact'])->name('contacts.destroy');


    // 3. GESTIÓN DE CUENTAS DE ALARMA (PANELES)
    // ----------------------------------------------------
    Route::resource('accounts', AccountController::class); 

    // Notas Operativas y Bitácora
    Route::put('accounts/{id}/notes', [AccountController::class, 'updateNotes'])->name('accounts.notes.update');
    Route::post('accounts/{id}/log', [AccountController::class, 'storeLog'])->name('accounts.log.store');

    // --- Sub-módulos de Configuración Técnica ---
    
    // A. Particiones
    Route::post('accounts/{id}/partitions', [AccountController::class, 'storePartition'])->name('accounts.partitions.store');
    Route::delete('partitions/{id}', [AccountController::class, 'destroyPartition'])->name('accounts.partitions.destroy');

    // B. Usuarios de Panel (Claves)
    Route::post('accounts/{id}/users', [AccountController::class, 'storePanelUser'])->name('accounts.users.store');
    Route::delete('panel-users/{id}', [AccountController::class, 'destroyPanelUser'])->name('accounts.users.destroy');

    // C. Horarios (Schedules)
    Route::post('accounts/{id}/schedules/temp', [AccountController::class, 'storeTempSchedule'])->name('accounts.schedules.temp.store');
    Route::post('accounts/{id}/schedules/weekly', [AccountController::class, 'storeWeeklySchedule'])->name('accounts.schedules.weekly.store');
    Route::delete('schedules/{id}', [AccountController::class, 'destroySchedule'])->name('schedules.destroy');

    // D. Zonas (Controlador Dedicado)
    Route::post('accounts/{id}/zones', [AlarmZoneController::class, 'store'])->name('accounts.zones.store');
    Route::delete('zones/{id}', [AlarmZoneController::class, 'destroy'])->name('zones.destroy');


    // 4. MÓDULO DE OPERACIONES (MONITOREO ACTIVO)
    // ----------------------------------------------------
    Route::prefix('operations')->group(function () {
        
        // Consola de eventos (Cola de espera)
        Route::get('/', [IncidentController::class, 'console'])->name('operations.console');

        // Acciones sobre Incidentes
        // a. Tomar un evento -> Crea Ticket
        Route::post('/take/{id}', [IncidentController::class, 'take'])->name('incidents.take');

        // b. Crear Evento Manual (Ticket sin señal)
        Route::post('/manual-event', [IncidentController::class, 'storeManual'])->name('incidents.manual');
        
        // c. Pantalla de Gestión (Mapa, llamadas, bitácora)
        Route::get('/incident/{id}', [IncidentController::class, 'manage'])->name('operations.manage');
        
        // d. Poner en Espera (Hold)
        Route::post('/incident/{id}/hold', [IncidentController::class, 'hold'])->name('incidents.hold');
        
        // e. Cerrar Incidente
        Route::post('/incident/{id}/close', [IncidentController::class, 'close'])->name('incidents.close');

        // f. Agregar Nota Manual (Bitácora Viva)
        Route::post('/incident/{id}/note', [IncidentController::class, 'addNote'])->name('incidents.add-note');
    });


    // 5. CONFIGURACIÓN DEL SISTEMA
    // ----------------------------------------------------
    Route::resource('sia-codes', SiaCodeController::class);

    // Configuración Dinámica de Incidentes (Resoluciones y Motivos)
    Route::prefix('config')->name('config.')->group(function () {
        // Resoluciones
        Route::get('resolutions', [IncidentConfigController::class, 'indexResolutions'])->name('resolutions.index');
        Route::post('resolutions', [IncidentConfigController::class, 'storeResolution'])->name('resolutions.store');
        Route::delete('resolutions/{id}', [IncidentConfigController::class, 'destroyResolution'])->name('resolutions.destroy');
        
        // Motivos de Espera
        Route::get('hold-reasons', [IncidentConfigController::class, 'indexHoldReasons'])->name('hold-reasons.index');
        Route::post('hold-reasons', [IncidentConfigController::class, 'storeHoldReason'])->name('hold-reasons.store');
        Route::delete('hold-reasons/{id}', [IncidentConfigController::class, 'destroyHoldReason'])->name('hold-reasons.destroy');
    });

});

// Autenticación (Generadas por Laravel Breeze/Auth)
require __DIR__.'/auth.php';