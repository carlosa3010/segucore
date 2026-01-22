<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuardAppController; // Asegúrate de haber creado este controlador
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\GpsController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\BillingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde se registran las rutas API para tu aplicación.
| Estas rutas son cargadas por el RouteServiceProvider y asignadas
| al grupo de middleware "api".
|
*/

// 1. AUTENTICACIÓN (Pública)
Route::post('/login', [AuthController::class, 'login']);

// 2. RUTAS PROTEGIDAS (Requieren Token Bearer)
Route::middleware('auth:sanctum')->group(function () {

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Obtener usuario actual
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ==========================================
    // MÓDULO APP DE GUARDIAS (Seguridad Física)
    // ==========================================
    Route::prefix('guard')->group(function () {
        // Estado del guardia y su patrulla asignada
        Route::get('/status', [GuardAppController::class, 'status']);
        
        // Marcar entrada/salida de turno (On Duty)
        Route::post('/duty', [GuardAppController::class, 'toggleDuty']);
        
        // 🚨 BOTÓN DE PÁNICO
        Route::post('/panic', [GuardAppController::class, 'panic']);
        
        // Tracking GPS del guardia (lat/lng)
        Route::post('/location', [GuardAppController::class, 'updateLocation']);
        
        // Obtener rondas asignadas
        Route::get('/rounds', [GuardAppController::class, 'rounds']);
        
        // Incidentes asignados a su patrulla (para atender)
        Route::get('/incidents', [GuardAppController::class, 'myIncidents']);
    });

    // ==========================================
    // OTROS MÓDULOS DEL SISTEMA
    // ==========================================
    
    // Gestión de Incidentes
    Route::apiResource('incidents', IncidentController::class);

    // Datos GPS / Rastreo
    Route::get('/gps/positions', [GpsController::class, 'positions']);
    Route::apiResource('gps', GpsController::class);

    // Clientes
    Route::apiResource('customers', CustomerController::class);

    // Facturación
    Route::apiResource('billing', BillingController::class);

});