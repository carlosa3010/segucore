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
        Schema::table('alarm_events', function (Blueprint $table) {
            // Agregamos las columnas que faltan y causan el error
            $table->string('account_number')->nullable()->after('alarm_account_id')->index();
            $table->string('event_type')->default('signal')->after('event_code'); // Ej: 'manual', 'signal', 'keepalive'
            $table->string('ip_address')->nullable()->after('partition');
            $table->timestamp('processed_at')->nullable()->after('processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alarm_events', function (Blueprint $table) {
            $table->dropColumn(['account_number', 'event_type', 'ip_address', 'processed_at']);
        });
    }
};