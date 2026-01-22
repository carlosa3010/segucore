<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Ajustes Generales (Key-Value para Empresa y APIs)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ej: 'company_name', 'google_maps_key'
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // 'general', 'api', 'mail'
            $table->timestamps();
        });

        // 2. Actualizar Tabla Users (Roles)
        Schema::table('users', function (Blueprint $table) {
            // admin, supervisor, operator, client
            $table->string('role')->default('operator')->after('email'); 
            $table->boolean('is_active')->default(true)->after('role');
        });

        // 3. Actualizar Códigos SIA (Lógica avanzada)
        Schema::table('sia_codes', function (Blueprint $table) {
            $table->text('procedure_instructions')->nullable(); // Qué hacer cuando llega este código
            
            // Para control de horarios (Aperturas/Cierres)
            $table->boolean('requires_schedule_check')->default(false); 
            $table->integer('schedule_grace_minutes')->default(30); // Tolerancia +/- minutos
            
            // Si llega fuera de horario o no llega:
            $table->enum('schedule_violation_action', ['none', 'warning', 'critical_alert'])->default('none');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
        Schema::table('sia_codes', function (Blueprint $table) {
            $table->dropColumn(['procedure_instructions', 'requires_schedule_check', 'schedule_grace_minutes', 'schedule_violation_action']);
        });
    }
};