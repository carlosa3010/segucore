<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            // Intervalo esperado en minutos (Ej: 1440 para 24 horas). Default 24h + 1h de gracia
            $table->integer('test_interval_minutes')->default(1440)->after('monitoring_status');
            
            // Para controlar cuándo fue la última vez que generamos este evento y no spammear
            $table->timestamp('last_connection_failure_at')->nullable()->after('last_signal_at');
        });
    }

    public function down(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->dropColumn(['test_interval_minutes', 'last_connection_failure_at']);
        });
    }
};