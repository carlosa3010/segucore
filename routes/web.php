<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;

// Controladores del Panel Admin (Namespaces actualizados)
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\IncidentController;

// ====================================================
// FRONTEND PÚBLICO / VIDEO WALL
// ====================================================

Route::get('/', function () { return view('welcome'); });

// Pantallas del Centro de Monitoreo
Route::get('/monitor', [MonitoringController::class, 'index'])->name('monitor.index');
Route::get('/mapa', [MonitoringController::class, 'map'])->name('monitor.map');

// API Interna (Alimenta al JS del Dashboard y Mapa)
Route::get('/api/live-events', [MonitoringController::class, 'getLiveEvents'])->name('api.live-events');


// ====================================================
// PANEL ADMINISTRATIVO Y OPERADOR
// ====================================================
// Todo lo que esté aquí requiere prefijo /admin
// A futuro: ->middleware(['auth'])

Route::prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard Principal (KPIs y Resumen)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Gestión de Clientes (CRM Completo)
    Route::resource('customers', CustomerController::class);

    // 3. Gestión de Cuentas de Alarma (Paneles)
    // Estas rutas permiten vincular un número de abonado a un cliente
    Route::get('accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');

    // 4. Consola de Operaciones (Área de Trabajo del Operador)
    Route::get('/operations', [IncidentController::class, 'console'])->name('operations.console');

    // 5. Flujo de Incidentes (Tickets)
    // Paso 1: Operador hace clic en "Atender" (Crea el ticket y asigna usuario)
    Route::post('/incidents/{id}/take', [IncidentController::class, 'take'])->name('incidents.take');
    
    // Paso 2: Pantalla de Gestión Activa (Bitácora, Llamadas, Mapa)
    // NOTA: Debemos crear el método 'manage' en IncidentController a continuación
    Route::get('/incidents/{id}/manage', [IncidentController::class, 'manage'])->name('operations.manage');
    
    // Paso 3: Cerrar el incidente
    Route::post('/incidents/{id}/close', [IncidentController::class, 'close'])->name('incidents.close');
});