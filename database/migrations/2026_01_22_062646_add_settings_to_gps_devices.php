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
        Schema::table('gps_devices', function (Blueprint $table) {
            // Agregamos las columnas nuevas
            $table->integer('speed_limit')->default(80)->after('plate_number'); // Límite de velocidad
            $table->decimal('odometer', 10, 2)->default(0)->after('speed_limit'); // Odómetro virtual
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_devices', function (Blueprint $table) {
            // Eliminamos las columnas si se revierte la migración
            $table->dropColumn(['speed_limit', 'odometer']);
        });
    }
};
