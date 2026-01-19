<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            // Agregamos el campo para el modelo del panel
            // Lo ponemos después de 'service_status' o al final
            $table->string('device_model', 100)->nullable()->after('service_status');
        });
    }

    public function down(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->dropColumn('device_model');
        });
    }
};