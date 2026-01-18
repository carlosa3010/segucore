<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Arreglar Horarios (Faltaba 'type')
        if (!Schema::hasColumn('account_schedules', 'type')) {
            Schema::table('account_schedules', function (Blueprint $table) {
                $table->string('type')->default('weekly')->after('alarm_account_id'); // weekly, temporary
            });
        }

        // 2. Arreglar Usuarios de Panel (Faltaba 'user_number')
        if (!Schema::hasColumn('panel_users', 'user_number')) {
            Schema::table('panel_users', function (Blueprint $table) {
                $table->string('user_number', 10)->after('alarm_account_id');
            });
        }

        // 3. Crear Tabla BITÁCORA (Historial de acciones)
        Schema::create('account_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alarm_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Quién escribió la nota (Operador)
            $table->string('type'); // 'call', 'note', 'alert'
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_logs');
        
        if (Schema::hasColumn('account_schedules', 'type')) {
            Schema::table('account_schedules', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
        
        if (Schema::hasColumn('panel_users', 'user_number')) {
            Schema::table('panel_users', function (Blueprint $table) {
                $table->dropColumn('user_number');
            });
        }
    }
};