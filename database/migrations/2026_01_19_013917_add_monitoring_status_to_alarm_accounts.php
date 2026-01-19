<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            
            // Solo agregar 'is_armed' si NO existe
            if (!Schema::hasColumn('alarm_accounts', 'is_armed')) {
                $table->boolean('is_armed')->default(false)->after('service_status');
            }

            // Solo agregar 'last_checkin_at' si NO existe
            if (!Schema::hasColumn('alarm_accounts', 'last_checkin_at')) {
                $table->timestamp('last_checkin_at')->nullable()->after('is_armed');
            }

            // ESTA ES LA QUE FALTA: Solo agregar 'last_signal_at' si NO existe
            if (!Schema::hasColumn('alarm_accounts', 'last_signal_at')) {
                $table->timestamp('last_signal_at')->nullable()->after('last_checkin_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            // Eliminar solo si existen (para evitar errores al revertir)
            $columns = [];
            if (Schema::hasColumn('alarm_accounts', 'is_armed')) $columns[] = 'is_armed';
            if (Schema::hasColumn('alarm_accounts', 'last_checkin_at')) $columns[] = 'last_checkin_at';
            if (Schema::hasColumn('alarm_accounts', 'last_signal_at')) $columns[] = 'last_signal_at';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};