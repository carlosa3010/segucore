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
*/
Route::domain('segusmart24.com')->group(function () {
    Route::get('/', function () { return view('welcome'); });

    Route::get('/verify/report/{id}', [PublicReportController::class, 'verify'])
        ->name('report.verify')
        ->middleware('signed');
});

/*
|==========================================================================
| 2. SUBDOMINIO ADMIN (admin.segusmart24.com)
|==========================================================================
*/
Route::domain('admin.segusmart24.com')->group(function () {

    Route::get('/', function () { return redirect()->route('login'); });

    require __DIR__.'/auth.php';

    Route::middleware(['auth'])->name('admin.')->group(function () {
        
        // 1. DASHBOARD PRINCIPAL
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // 2. GESTIÓN DE CLIENTES (CRM)
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        
        Route::post('customers/{id}/contacts', [CustomerController::class, 'storeContact'])->name('customers.contacts.store');
        Route::put('contacts/{id}', [CustomerController::class, 'updateContact'])->name('contacts.update');
        Route::delete('contacts/{id}', [CustomerController::class, 'destroyContact'])->name('contacts.destroy');

        // 3. GESTIÓN DE CUENTAS DE ALARMA
        Route::resource('accounts', AccountController::class); 
        Route::put('accounts/{id}/notes', [AccountController::class, 'updateNotes'])->name('accounts.notes.update');
        Route::post('accounts/{id}/log', [AccountController::class, 'storeLog'])->name('accounts.log.store');

        Route::post('accounts/{id}/partitions', [AccountController::class, 'storePartition'])->name('accounts.partitions.store');
        Route::put('partitions/{id}', [AccountController::class, 'updatePartition'])->name('partitions.update');
        Route::delete('partitions/{id}', [AccountController::class, 'destroyPartition'])->name('partitions.destroy');

        Route::post('accounts/{id}/users', [AccountController::class, 'storePanelUser'])->name('accounts.users.store');
        Route::put('panel-users/{id}', [AccountController::class, 'updatePanelUser'])->name('accounts.users.update');
        Route::delete('panel-users/{id}', [AccountController::class, 'destroyPanelUser'])->name('accounts.users.destroy');

        Route::post('accounts/{id}/zones', [AlarmZoneController::class, 'store'])->name('accounts.zones.store');
        Route::put('zones/{id}', [AlarmZoneController::class, 'update'])->name('zones.update');
        Route::delete('zones/{id}', [AlarmZoneController::class, 'destroy'])->name('zones.destroy');

        // 4. MÓDULO DE OPERACIONES (Consola)
        Route::prefix('operations')->group(function () {
            Route::get('/', [IncidentController::class, 'console'])->name('operations.console');
            Route::post('/take/{id}', [IncidentController::class, 'take'])->name('incidents.take');
            Route::get('/incident/{id}', [IncidentController::class, 'manage'])->name('operations.manage');
            Route::post('/incident/{id}/close', [IncidentController::class, 'close'])->name('incidents.close');
        });

        // 5. CONFIGURACIÓN DEL SISTEMA
        Route::resource('sia-codes', SiaCodeController::class);
        Route::resource('users', UserController::class);
        Route::get('profile/password', [UserController::class, 'changePasswordView'])->name('profile.password');
        Route::post('profile/password', [UserController::class, 'updatePassword'])->name('profile.password.update');

        Route::prefix('config')->name('config.')->group(function () {
            Route::get('general', [GeneralSettingController::class, 'index'])->name('general.index');
            Route::post('general', [GeneralSettingController::class, 'update'])->name('general.update');

            // Resoluciones y Planes
            Route::resource('plans', ServicePlanController::class);
            Route::resource('resolutions', IncidentConfigController::class)->only(['index', 'store', 'destroy']);
        });

        // 6. ADMINISTRACIÓN Y FINANZAS
        // Facturación
        Route::get('invoices/create/{customer_id}', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::resource('invoices', InvoiceController::class)->except(['create']);

        // Pagos y Tasas
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [FinanceController::class, 'index'])->name('index');
            Route::post('/rate', [FinanceController::class, 'updateRate'])->name('rate.update');
            Route::post('/payment', [FinanceController::class, 'storePayment'])->name('payment.store');
        });

        // 7. RASTREO GPS Y FLOTAS
        Route::prefix('gps')->name('gps.')->group(function () {
            Route::resource('devices', GpsDeviceController::class);
            Route::get('/fleet', [FleetController::class, 'index'])->name('fleet.index');
            Route::get('devices/{id}/history', [GpsDeviceController::class, 'history'])->name('devices.history');
        });

        Route::resource('drivers', DriverController::class);
        Route::resource('geofences', GeofenceController::class);
        Route::get('alerts', [DeviceAlertController::class, 'index'])->name('alerts.index');

        // 8. SEGURIDAD FÍSICA
        Route::resource('patrols', PatrolController::class);
        Route::resource('guards', GuardController::class);
        Route::get('security-map', [SecurityMapController::class, 'index'])->name('security.map.index');

        // 9. REPORTES
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });
});

/*
|==========================================================================
| 3. SUBDOMINIO CLIENTE (cliente.segusmart24.com)
|==========================================================================
*/
Route::domain('cliente.segusmart24.com')->group(function () {
    Route::get('login', [\App\Http\Controllers\Auth\ClientLoginController::class, 'create'])->name('client.login');
    Route::post('login', [\App\Http\Controllers\Auth\ClientLoginController::class, 'store']);
    Route::post('logout', [\App\Http\Controllers\Auth\ClientLoginController::class, 'destroy'])->name('client.logout');

    Route::middleware(['auth'])->group(function () {
        Route::get('/', [ClientPortalController::class, 'index'])->name('client.dashboard');
        Route::get('/modal/alarm/{id}', [ClientPortalController::class, 'modalAlarm'])->name('client.modal.alarm');
        Route::get('/modal/gps/{id}', [ClientPortalController::class, 'modalGps'])->name('client.modal.gps');
    });
});

/*
|==========================================================================
| 4. VIDEO WALLS
|==========================================================================
*/
Route::domain('map.segusmart24.com')->middleware('video.wall')->group(function () {
    Route::get('/', [MonitoringController::class, 'map'])->name('monitor.map');
});

Route::domain('panel.segusmart24.com')->middleware('video.wall')->group(function () {
    Route::get('/', [MonitoringController::class, 'index'])->name('monitor.index');
});