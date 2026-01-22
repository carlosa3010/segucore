<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Permitir GPS sin cliente (Uso Interno)
        Schema::table('gps_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });

        // 2. Agregar campos de rastreo a la tabla de Guardias (Para la App)
        Schema::table('guards', function (Blueprint $table) {
            $table->decimal('last_lat', 10, 7)->nullable();
            $table->decimal('last_lng', 10, 7)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->integer('battery_level')->nullable(); // Nivel de batería del celular
        });
    }

    public function down(): void
    {
        Schema::table('gps_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
        
        Schema::table('guards', function (Blueprint $table) {
            $table->dropColumn(['last_lat', 'last_lng', 'last_seen_at', 'battery_level']);
        });
    }
};
