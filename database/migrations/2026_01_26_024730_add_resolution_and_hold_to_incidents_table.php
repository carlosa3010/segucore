<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Paso 1: Asegurar que exista la columna 'result' (que faltaba y causó el error)
        if (!Schema::hasColumn('incidents', 'result')) {
            Schema::table('incidents', function (Blueprint $table) {
                // La agregamos después de 'status' si existe, si no, al final.
                $table->string('result')->nullable(); 
            });
        }

        // Paso 2: Crear las relaciones
        Schema::table('incidents', function (Blueprint $table) {
            // Relación con Resoluciones (Cierre)
            $table->foreignId('incident_resolution_id')
                  ->nullable()
                  // Quitamos el 'after' estricto para evitar errores, se agregarán al final
                  ->constrained('incident_resolutions')
                  ->nullOnDelete();

            // Relación con Motivos de Espera
            $table->foreignId('incident_hold_reason_id')
                  ->nullable()
                  ->constrained('incident_hold_reasons')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // Eliminamos las llaves foráneas y columnas
            $table->dropForeign(['incident_resolution_id']);
            $table->dropColumn('incident_resolution_id');

            $table->dropForeign(['incident_hold_reason_id']);
            $table->dropColumn('incident_hold_reason_id');
            
            // Opcional: Si quieres que el rollback también borre 'result', descomenta esto:
            // if (Schema::hasColumn('incidents', 'result')) {
            //    $table->dropColumn('result');
            // }
        });
    }
};