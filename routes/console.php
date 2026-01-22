<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * --------------------------------------------------------------------------
 * TAREAS PROGRAMADAS (CRON JOBS)
 * --------------------------------------------------------------------------
 */

// Verificar alertas de GPS (Exceso de velocidad, etc.) cada minuto
Schedule::command('gps:check-alerts')->everyMinute();

// Limpiar tokens de API expirados diariamente (Mantenimiento)
Schedule::command('sanctum:prune-expired')->daily();