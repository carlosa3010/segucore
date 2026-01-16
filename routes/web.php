<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;

/*
|--------------------------------------------------------------------------
| Web Routes - SeguSmart Core
|--------------------------------------------------------------------------
|
| Aquí se definen las rutas para el Video Wall y el sistema de monitoreo.
|
*/

// --- 1. PÁGINA DE INICIO ---
Route::get('/', function () {
    return view('welcome');
});

// --- 2. VIDEO WALL: PANTALLA 1 (LISTA DE EVENTOS) ---
// Esta ruta carga la vista de listado "tipo cascada" para el operador
Route::get('/monitor', [MonitoringController::class, 'index'])->name('monitor.index');

// --- 3. VIDEO WALL: PANTALLA 2 (MAPA TÁCTICO) ---
// Esta ruta carga la vista del Mapa Oscuro a pantalla completa
Route::get('/mapa', [MonitoringController::class, 'map'])->name('monitor.map');

// --- 4. API DEL NÚCLEO (CEREBRO) ---
// Esta ruta alimenta con datos JSON a ambas pantallas (Lista y Mapa)
// También se encargará de traer las coordenadas GPS cuando integremos los datos de Traccar en el controlador
Route::get('/api/live-events', [MonitoringController::class, 'getLiveEvents'])->name('api.live-events');