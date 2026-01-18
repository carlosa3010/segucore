<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;

// Controladores del Panel Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\IncidentController;
use App\Http\Controllers\Admin\SiaCodeController;
use App\Http\Controllers\Admin\AlarmZoneController;

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

Route::prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard Principal (KPIs y Resumen)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Gestión de Clientes (CRM Completo)
    Route::resource('customers', CustomerController::class);
    
    // 2.1 Acción Extra: Suspender/Reactivar Cliente (y servicios en cascada)
    Route::post('customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

    // 3. Gestión de Cuentas de Alarma (Paneles)
    // ----------------------------------------------------
    // Listado General
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');

    // Creación
    Route::get('accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
    
    // Ficha Técnica, Edición y Eliminación
    Route::get('accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    Route::put('accounts/{id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('accounts/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy'); // <--- ESTA FALTABA

    // Actualización de Notas Operativas (Permanentes/Temporales)
    Route::put('accounts/{id}/notes', [AccountController::class, 'updateNotes'])->name('accounts.notes.update');

    // Gestión de Particiones (Sub-recurso)
    Route::post('accounts/{id}/partitions', [AccountController::class, 'storePartition'])->name('accounts.partitions.store');

    // Gestión de Zonas (Sensores)
    Route::post('accounts/{id}/zones', [AlarmZoneController::class, 'store'])->name('accounts.zones.store');
    Route::delete('zones/{id}', [AlarmZoneController::class, 'destroy'])->name('zones.destroy');


    // 4. Consola de Operaciones (Área de Trabajo del Operador)
    Route::get('/operations', [IncidentController::class, 'console'])->name('operations.console');

    // 5. Flujo de Incidentes (Tickets)
    // Paso 1: Operador hace clic en "Atender" (Crea el ticket y asigna usuario)
    Route::post('/incidents/{id}/take', [IncidentController::class, 'take'])->name('incidents.take');
    
    // Paso 2: Pantalla de Gestión Activa (Bitácora, Llamadas, Mapa)
    Route::get('/incidents/{id}/manage', [IncidentController::class, 'manage'])->name('operations.manage');
    
    // Paso 3: Cerrar el incidente
    Route::post('/incidents/{id}/close', [IncidentController::class, 'close'])->name('incidents.close');

    // 6. Configuración del Sistema
    // Gestión de Códigos SIA (Diccionario de Eventos)
    Route::resource('sia-codes', SiaCodeController::class);
});