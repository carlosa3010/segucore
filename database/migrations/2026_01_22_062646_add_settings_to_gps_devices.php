<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gps_devices', function (Blueprint $table) {
            // Verifica si las columnas ya existen para evitar error de duplicado
            if (!Schema::hasColumn('gps_devices', 'speed_limit')) {
                $table->integer('speed_limit')->default(80)->after('plate_number');
            }
            if (!Schema::hasColumn('gps_devices', 'odometer')) {
                $table->decimal('odometer', 10, 2)->default(0)->after('speed_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gps_devices', function (Blueprint $table) {
            // Verifica si existen antes de intentar borrarlas
            if (Schema::hasColumn('gps_devices', 'speed_limit')) {
                $table->dropColumn('speed_limit');
            }
            if (Schema::hasColumn('gps_devices', 'odometer')) {
                $table->dropColumn('odometer');
            }
        });
    }
};