<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\IncidentController;

// --- FRONT VIDEO WALL (Público o con token simple) ---
Route::get('/', function () { return view('welcome'); });
Route::get('/monitor', [MonitoringController::class, 'index'])->name('monitor.index');
Route::get('/mapa', [MonitoringController::class, 'map'])->name('monitor.map');

// API Interna para el Video Wall (Ya existente)
Route::get('/api/live-events', [MonitoringController::class, 'getLiveEvents'])->name('api.live-events');

// --- PANEL ADMINISTRATIVO Y OPERADOR (Protegido) ---
// En el futuro agregaremos ->middleware(['auth']) aquí
Route::prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard Principal
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Gestión de Clientes (CRM)
    Route::resource('customers', CustomerController::class);

    // 3. Consola de Operaciones (Gestión de Incidentes)
    Route::get('/operations', [IncidentController::class, 'console'])->name('operations.console');
    Route::post('/incidents/{id}/take', [IncidentController::class, 'take'])->name('incidents.take');
    Route::post('/incidents/{id}/close', [IncidentController::class, 'close'])->name('incidents.close');
});