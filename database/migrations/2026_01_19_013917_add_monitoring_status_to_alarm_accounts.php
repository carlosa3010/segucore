<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->boolean('is_armed')->default(false)->after('service_status'); // ¿Está armado?
            $table->timestamp('last_checkin_at')->nullable()->after('is_armed'); // Último Test (RP)
            $table->timestamp('last_signal_at')->nullable()->after('last_checkin_at'); // Última señal cualquiera
        });
    }

    public function down(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->dropColumn(['is_armed', 'last_checkin_at', 'last_signal_at']);
        });
    }
};