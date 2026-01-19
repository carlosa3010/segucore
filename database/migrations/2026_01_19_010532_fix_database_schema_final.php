<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Arreglar Tabla Usuarios de Panel
        Schema::table('panel_users', function (Blueprint $table) {
            // Eliminamos la columna vieja que causa conflicto
            if (Schema::hasColumn('panel_users', 'user_slot')) {
                $table->dropColumn('user_slot');
            }
            // Aseguramos que user_number exista
            if (!Schema::hasColumn('panel_users', 'user_number')) {
                $table->string('user_number', 10)->after('alarm_account_id');
            }
        });

        // 2. Arreglar Tabla Horarios
        Schema::table('account_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('account_schedules', 'reason')) {
                $table->string('reason')->nullable()->after('close_time');
            }
            if (!Schema::hasColumn('account_schedules', 'valid_until')) {
                $table->dateTime('valid_until')->nullable()->after('reason');
            }
            // Hacemos columnas opcionales para evitar errores
            $table->time('open_time')->nullable()->change();
            $table->time('close_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('account_schedules', function (Blueprint $table) {
            $table->dropColumn(['reason', 'valid_until']);
        });
    }
};
