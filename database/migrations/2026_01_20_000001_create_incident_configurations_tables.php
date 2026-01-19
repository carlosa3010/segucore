<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Resoluciones (Cierre)
        Schema::create('incident_resolutions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Falsa Alarma
            $table->string('code')->unique(); // Ej: false_alarm
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tabla de Motivos de Espera (Hold)
        Schema::create('incident_hold_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Policía en Camino
            $table->string('code')->unique(); // Ej: police_dispatched
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Insertar Datos Iniciales (Seed inmediato)
        DB::table('incident_resolutions')->insert([
            ['name' => '🚫 Falsa Alarma', 'code' => 'false_alarm', 'created_at' => now()],
            ['name' => '👮 Real - Policía Actuó', 'code' => 'real_police', 'created_at' => now()],
            ['name' => '🚑 Real - Emergencia Médica', 'code' => 'real_medical', 'created_at' => now()],
            ['name' => '🔧 Prueba de Usuario', 'code' => 'test', 'created_at' => now()],
        ]);

        DB::table('incident_hold_reasons')->insert([
            ['name' => '⏳ Monitoreo Preventivo (Cliente avisado)', 'code' => 'monitoring', 'created_at' => now()],
            ['name' => '🚓 Policía en Camino', 'code' => 'police_dispatched', 'created_at' => now()],
            ['name' => '📞 Esperando Respuesta de Contacto', 'code' => 'waiting_contact', 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_resolutions');
        Schema::dropIfExists('incident_hold_reasons');
    }
};