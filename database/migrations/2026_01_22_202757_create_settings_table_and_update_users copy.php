<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Ajustes Generales (Verificar existencia)
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique(); // Ej: 'company_name', 'google_maps_key'
                $table->text('value')->nullable();
                $table->string('group')->default('general'); // 'general', 'api', 'mail'
                $table->timestamps();
            });
        }

        // 2. Actualizar Tabla Users (Verificar columnas)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('operator')->after('email'); 
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password'); // Ajustado after
            }
        });

        // 3. Actualizar Códigos SIA (Verificar columnas)
        Schema::table('sia_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('sia_codes', 'procedure_instructions')) {
                $table->text('procedure_instructions')->nullable(); 
            }
            if (!Schema::hasColumn('sia_codes', 'requires_schedule_check')) {
                $table->boolean('requires_schedule_check')->default(false); 
            }
            if (!Schema::hasColumn('sia_codes', 'schedule_grace_minutes')) {
                $table->integer('schedule_grace_minutes')->default(30); 
            }
            if (!Schema::hasColumn('sia_codes', 'schedule_violation_action')) {
                $table->enum('schedule_violation_action', ['none', 'warning', 'critical_alert'])->default('none');
            }
        });
    }

    public function down(): void
    {
        // Revertir cambios de forma segura
        Schema::dropIfExists('settings');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) $table->dropColumn('role');
            if (Schema::hasColumn('users', 'is_active')) $table->dropColumn('is_active');
        });

        Schema::table('sia_codes', function (Blueprint $table) {
            $columns = ['procedure_instructions', 'requires_schedule_check', 'schedule_grace_minutes', 'schedule_violation_action'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('sia_codes', $col)) $table->dropColumn($col);
            }
        });
    }
};