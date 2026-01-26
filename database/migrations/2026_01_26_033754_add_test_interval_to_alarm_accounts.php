<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            
            // 1. REPARACIÓN: Crear columnas de monitoreo si faltan
            // Estas columnas están en el modelo pero no existían en la BD
            if (!Schema::hasColumn('alarm_accounts', 'is_armed')) {
                // Lo ponemos después de 'is_active' o al final si no te importa el orden
                $table->boolean('is_armed')->default(false)->after('is_active'); 
            }
            
            if (!Schema::hasColumn('alarm_accounts', 'last_checkin_at')) {
                $table->timestamp('last_checkin_at')->nullable()->after('is_armed');
            }

            if (!Schema::hasColumn('alarm_accounts', 'last_signal_at')) {
                $table->timestamp('last_signal_at')->nullable()->after('last_checkin_at');
            }

            // 2. NUEVA FUNCIONALIDAD: Intervalos de Test
            if (!Schema::hasColumn('alarm_accounts', 'test_interval_minutes')) {
                $table->integer('test_interval_minutes')->default(1440)->after('monitoring_status');
            }

            if (!Schema::hasColumn('alarm_accounts', 'last_connection_failure_at')) {
                // Ahora sí podemos usar 'after last_signal_at' porque acabamos de asegurar que existe
                $table->timestamp('last_connection_failure_at')->nullable()->after('last_signal_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            // Eliminamos las columnas creadas
            $table->dropColumn([
                'test_interval_minutes', 
                'last_connection_failure_at',
                'last_signal_at',
                'last_checkin_at',
                'is_armed'
            ]);
        });
    }
};