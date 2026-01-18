<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;

// Controladores del Panel Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AccountController; // Lógica centralizada aquí
use App\Http\Controllers\Admin\IncidentController;
use App\Http\Controllers\Admin\SiaCodeController;
use App\Http\Controllers\Admin\AlarmZoneController; // Zonas se mantiene separado

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

Route::prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard Principal (KPIs y Resumen)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Gestión de Clientes (CRM Completo)
    Route::resource('customers', CustomerController::class);
    
    // Acciones extra de Clientes
    Route::post('customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    
    // Contactos de Emergencia (Gestión dentro de Clientes o Cuentas)
    Route::post('customers/{id}/contacts', [CustomerController::class, 'storeContact'])->name('customers.contacts.store');
    Route::delete('contacts/{id}', [CustomerController::class, 'destroyContact'])->name('contacts.destroy');


    // 3. Gestión de Cuentas de Alarma (Paneles)
    // ----------------------------------------------------
    // CRUD Principal
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    Route::put('accounts/{id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('accounts/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    // Notas Operativas y Bitácora
    Route::put('accounts/{id}/notes', [AccountController::class, 'updateNotes'])->name('accounts.notes.update');
    Route::post('accounts/{id}/log', [AccountController::class, 'storeLog'])->name('accounts.log.store'); // <--- NUEVA RUTA PARA BITÁCORA

    // SUB-MÓDULOS DE CUENTA (Centralizados en AccountController)
    
    // A. Particiones
    Route::post('accounts/{id}/partitions', [AccountController::class, 'storePartition'])->name('accounts.partitions.store');
    Route::delete('partitions/{id}', [AccountController::class, 'destroyPartition'])->name('partitions.destroy');

    // B. Usuarios de Panel (Claves)
    Route::post('accounts/{id}/users', [AccountController::class, 'storePanelUser'])->name('accounts.users.store');
    Route::delete('panel-users/{id}', [AccountController::class, 'destroyPanelUser'])->name('accounts.users.destroy');

    // C. Horarios (Schedules)
    Route::post('accounts/{id}/schedules/temp', [AccountController::class, 'storeTempSchedule'])->name('accounts.schedules.temp.store');
    Route::delete('schedules/{id}', [AccountController::class, 'destroySchedule'])->name('schedules.destroy');

    // D. Zonas / Sensores (Usa controlador dedicado AlarmZoneController)
    Route::post('accounts/{id}/zones', [AlarmZoneController::class, 'store'])->name('accounts.zones.store');
    Route::delete('zones/{id}', [AlarmZoneController::class, 'destroy'])->name('zones.destroy');


    // 4. Consola de Operaciones (Área de Trabajo del Operador)
    Route::get('/operations', [IncidentController::class, 'console'])->name('operations.console');

    // 5. Flujo de Incidentes (Tickets)
    Route::post('/incidents/{id}/take', [IncidentController::class, 'take'])->name('incidents.take');
    Route::get('/incidents/{id}/manage', [IncidentController::class, 'manage'])->name('operations.manage');
    Route::post('/incidents/{id}/close', [IncidentController::class, 'close'])->name('incidents.close');

    // 6. Configuración del Sistema (Códigos SIA)
    Route::resource('sia-codes', SiaCodeController::class);
});