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
        Schema::table('incident_hold_reasons', function (Blueprint $table) {
            // 1. Agregar la columna 'code' que falta
            if (!Schema::hasColumn('incident_hold_reasons', 'code')) {
                $table->string('code')->nullable()->unique()->after('id'); 
                // La creamos nullable temporalmente por si hay datos, pero idealmente debe ser única
            }

            // 2. Renombrar 'reason' a 'name' para que coincida con tu Controlador
            if (Schema::hasColumn('incident_hold_reasons', 'reason')) {
                $table->renameColumn('reason', 'name');
            } else {
                // Si por alguna razón no existía 'reason', creamos 'name'
                if (!Schema::hasColumn('incident_hold_reasons', 'name')) {
                    $table->string('name')->after('id');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_hold_reasons', function (Blueprint $table) {
            // Revertir cambios
            if (Schema::hasColumn('incident_hold_reasons', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('incident_hold_reasons', 'name')) {
                $table->renameColumn('name', 'reason');
            }
        });
    }
};