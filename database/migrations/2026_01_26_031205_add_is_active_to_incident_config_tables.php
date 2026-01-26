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
        // 1. Agregar is_active a Resoluciones
        Schema::table('incident_resolutions', function (Blueprint $table) {
            if (!Schema::hasColumn('incident_resolutions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('code');
            }
        });

        // 2. Agregar is_active a Motivos de Espera
        Schema::table('incident_hold_reasons', function (Blueprint $table) {
            if (!Schema::hasColumn('incident_hold_reasons', 'is_active')) {
                // Lo ponemos al final porque la estructura varió con migraciones anteriores
                $table->boolean('is_active')->default(true); 
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_resolutions', function (Blueprint $table) {
            if (Schema::hasColumn('incident_resolutions', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('incident_hold_reasons', function (Blueprint $table) {
            if (Schema::hasColumn('incident_hold_reasons', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};