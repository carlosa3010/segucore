<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PublicReportController;
use App\Http\Controllers\ClientPortalController;

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
use App\Http\Controllers\Admin\GpsDeviceController;
use App\Http\Controllers\Admin\FleetController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\GeofenceController;
use App\Http\Controllers\Admin\DeviceAlertController;
use App\Http\Controllers\Admin\PatrolController;
use App\Http\Controllers\Admin\GuardController;
use App\Http\Controllers\Admin\SecurityMapController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\FinanceController;

/*
|==========================================================================
| 1. DOMINIO MAESTRO (segusmart24.com)
|==========================================================================
| Landing Page y Verificaciones Públicas
*/
Route::domain('segusmart24.com')->group(function () {
    
    // Landing Page
    Route::get('/', function () { return view('welcome'); });

    // Verificación Pública de Documentos (QR)
    Route::get('/verify/report/{id}', [PublicReportController::class, 'verify'])
        ->name('report.verify')
        ->middleware('signed');
});

/*
|==========================================================================
| 2. SUBDOMINIO ADMIN (admin.segusmart24.com)
|==========================================================================
| Panel de Gestión completo
*/
Route::domain('admin.segusmart24.com')->group(function () {

    // Redirección raíz al login
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // Rutas de Autenticación (Login, Logout, etc.)
    require __DIR__.'/auth.php';

    // GRUPO PROTEGIDO (Requiere Login)
    Route::middleware(['auth'])->name('admin.')->group(function () {
        
        // 1. DASHBOARD PRINCIPAL
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // 2. GESTIÓN DE CLIENTES (CRM)
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        
        // Contactos de Clientes
        Route::post('customers/{id}/contacts', [CustomerController::class, 'storeContact'])->name('customers.contacts.store');
        Route::put('contacts/{id}', [CustomerController::class, 'updateContact'])->name('contacts.update');
        Route::delete('contacts/{id}', [CustomerController::class, 'destroyContact'])->name('contacts.destroy');

        // 3. GESTIÓN DE CUENTAS DE ALARMA (PANELES)
        Route::resource('accounts', AccountController::class); 

        // Notas y Bitácora de Cuentas
        Route::put('accounts/{id}/notes', [AccountController::class, 'updateNotes'])->name('accounts.notes.update');
        Route::post('accounts/{id}/log', [AccountController::class, 'storeLog'])->name('accounts.log.store');

        // Sub-módulos de Cuentas
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

        // 4. MÓDULO DE OPERACIONES (Incidentes)
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

        // --- PERFIL DE USUARIO ---
        Route::get('profile/password', [UserController::class, 'changePasswordView'])->name('profile.password');
        Route::post('profile/password', [UserController::class, 'updatePassword'])->name('profile.password.update');

        Route::prefix('config')->name('config.')->group(function () {
            // --- AJUSTES GENERALES ---
            Route::get('general', [GeneralSettingController::class, 'index'])->name('general.index');
            Route::post('general', [GeneralSettingController::class, 'update'])->name('general.update');

            // Resoluciones
            Route::get('resolutions', [IncidentConfigController::class, 'indexResolutions'])->name('resolutions.index');
            Route::post('resolutions', [IncidentConfigController::class, 'storeResolution'])->name('resolutions.store');
            Route::delete('resolutions/{id}', [IncidentConfigController::class, 'destroyResolution'])->name('resolutions.destroy');
            
            Route::get('hold-reasons', [IncidentConfigController::class, 'indexHoldReasons'])->name('hold-reasons.index');
            Route::post('hold-reasons', [IncidentConfigController::class, 'storeHoldReason'])->name('hold-reasons.store');
            Route::delete('hold-reasons/{id}', [IncidentConfigController::class, 'destroyHoldReason'])->name('hold-reasons.destroy');

            // Planes de Facturación
            Route::resource('plans', ServicePlanController::class);
        });

        // 6. MÓDULO DE REPORTES
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/list', [ReportController::class, 'printList'])->name('list');
            Route::get('/summary', [ReportController::class, 'printSummary'])->name('summary');
            Route::get('/detail/{id}', [ReportController::class, 'detail'])->name('detail');
        });

        // 7. MÓDULO DE RASTREO GPS Y FLOTAS
        Route::prefix('gps')->name('gps.')->group(function () {
            Route::resource('devices', GpsDeviceController::class);
            
            // Comandos Remotos
            Route::post('devices/{id}/command', [GpsDeviceController::class, 'sendCommand'])->name('devices.command');

            // Historial de Ruta
            Route::get('devices/{id}/route', [GpsDeviceController::class, 'getRoute'])->name('devices.route');
            
            // Historial & Reportes
            Route::get('devices/{id}/history', [GpsDeviceController::class, 'history'])->name('devices.history');
            Route::get('devices/{id}/history-data', [GpsDeviceController::class, 'getHistoryData'])->name('devices.history-data');
            Route::get('devices/{id}/history/pdf', [GpsDeviceController::class, 'exportHistoryPdf'])->name('devices.history.pdf');

            // Flotas (Vista General)
            Route::get('/fleet', [FleetController::class, 'index'])->name('fleet.index');
            Route::get('/fleet/positions', [FleetController::class, 'positions'])->name('fleet.positions');
        });

        // 8. GESTIÓN DE CONDUCTORES
        Route::resource('drivers', DriverController::class);

        // 9. GESTIÓN DE GEOCERCAS
        Route::resource('geofences', GeofenceController::class);

        // 10. ALERTAS
        Route::get('alerts', [DeviceAlertController::class, 'index'])->name('alerts.index');

        // 11. SEGURIDAD FÍSICA
        Route::resource('patrols', PatrolController::class);
        Route::resource('guards', GuardController::class);
        
        // Mapa Táctico Operativo
        Route::get('security-map', [SecurityMapController::class, 'index'])->name('security.map.index');
        Route::get('security-map/data', [SecurityMapController::class, 'positions'])->name('security.map.data');

        // 12. FACTURACIÓN Y FINANZAS
        // Facturación detallada
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create'); 
        
        // ✅ Ruta para descargar PDF (Agregada para solucionar el error del botón)
        Route::get('invoices/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        
        // Resource estándar (Crea automáticamente la ruta show: admin.invoices.show)
        Route::resource('invoices', InvoiceController::class)->except(['create']);

        // Pagos y Tasas
        Route::prefix('finance')->name('finance.')->group(function() {
            Route::get('/', [FinanceController::class, 'index'])->name('index');
            Route::post('/rate', [FinanceController::class, 'updateRate'])->name('rate.update');
            Route::post('/payment', [FinanceController::class, 'storePayment'])->name('payment.store');
        });
    });
});

/*
|==========================================================================
| 3. SUBDOMINIO CLIENTE (cliente.segusmart24.com)
|==========================================================================
*/
Route::domain('cliente.segusmart24.com')->group(function () {
    
    // Rutas de Autenticación Cliente
    Route::middleware('guest')->group(function () {
        Route::get('login', [\App\Http\Controllers\Auth\ClientLoginController::class, 'create'])->name('client.login');
        Route::post('login', [\App\Http\Controllers\Auth\ClientLoginController::class, 'store']);
    });

    // Logout
    Route::post('logout', [\App\Http\Controllers\Auth\ClientLoginController::class, 'destroy'])->name('client.logout');

    Route::middleware(['auth'])->group(function () {
        // EL DASHBOARD (Mapa + Panel Lateral)
        Route::get('/', [ClientPortalController::class, 'index'])->name('client.dashboard');
        
        // APIs
        Route::get('/api/assets', [ClientPortalController::class, 'getAssets'])->name('client.api.assets');
        Route::get('/api/alerts', [ClientPortalController::class, 'getLatestAlerts'])->name('client.api.alerts');
        
        // Historial API
        Route::get('/api/history/{id}', [ClientPortalController::class, 'getHistory'])->name('client.api.history');
        
        // DESCARGAR PDF HISTORIAL
        Route::get('/api/history/{id}/pdf', [ClientPortalController::class, 'downloadReport'])->name('client.api.history.pdf');

        // COMANDOS REMOTOS
        Route::post('/api/device/{id}/command', [ClientPortalController::class, 'sendCommand'])->name('client.device.command');

        // Modales
        Route::get('/modal/alarm/{id}', [ClientPortalController::class, 'modalAlarm'])->name('client.modal.alarm');
        Route::get('/modal/gps/{id}', [ClientPortalController::class, 'modalGps'])->name('client.modal.gps');
        Route::get('/modal/billing', [ClientPortalController::class, 'modalBilling'])->name('client.modal.billing');
    });
});

/*
|==========================================================================
| 4. VIDEO WALL - MAPA TÁCTICO
|==========================================================================
*/
Route::domain('map.segusmart24.com')->middleware('video.wall')->group(function () {
    Route::get('/', [MonitoringController::class, 'map'])->name('monitor.map');
    Route::get('/api/live-events', [MonitoringController::class, 'getLiveEvents'])->name('api.live-events');
});

/*
|==========================================================================
| 5. VIDEO WALL - DASHBOARD GRID
|==========================================================================
*/
Route::domain('panel.segusmart24.com')->middleware('video.wall')->group(function () {
    Route::get('/', [MonitoringController::class, 'index'])->name('monitor.index');
});