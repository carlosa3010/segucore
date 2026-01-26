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
        Schema::table('incidents', function (Blueprint $table) {
            // Relación con Resoluciones (Cierre)
            // Se usa set null para que si borras una resolución, el historial no se rompa (solo queda null)
            $table->foreignId('incident_resolution_id')
                  ->nullable()
                  ->after('result') // Opcional: para ordenarlo visualmente después de 'result'
                  ->constrained('incident_resolutions')
                  ->nullOnDelete();

            // Relación con Motivos de Espera
            $table->foreignId('incident_hold_reason_id')
                  ->nullable()
                  ->after('incident_resolution_id')
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
            // Eliminamos primero la llave foránea y luego la columna
            $table->dropForeign(['incident_resolution_id']);
            $table->dropColumn('incident_resolution_id');

            $table->dropForeign(['incident_hold_reason_id']);
            $table->dropColumn('incident_hold_reason_id');
        });
    }
};
