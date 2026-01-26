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
        Schema::table('incident_logs', function (Blueprint $table) {
            // 1. Renombrar 'type' a 'action_type' para coincidir con el Modelo
            if (Schema::hasColumn('incident_logs', 'type') && !Schema::hasColumn('incident_logs', 'action_type')) {
                $table->renameColumn('type', 'action_type');
            }

            // 2. Renombrar 'action' a 'description' para coincidir con el Modelo
            if (Schema::hasColumn('incident_logs', 'action') && !Schema::hasColumn('incident_logs', 'description')) {
                $table->renameColumn('action', 'description');
            }

            // 3. Agregar columnas faltantes (Soportadas en tu $fillable)
            if (!Schema::hasColumn('incident_logs', 'sip_call_id')) {
                $table->string('sip_call_id')->nullable()->after('description');
            }
            if (!Schema::hasColumn('incident_logs', 'recording_url')) {
                $table->string('recording_url')->nullable()->after('sip_call_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_logs', function (Blueprint $table) {
            if (Schema::hasColumn('incident_logs', 'action_type')) {
                $table->renameColumn('action_type', 'type');
            }
            if (Schema::hasColumn('incident_logs', 'description')) {
                $table->renameColumn('description', 'action');
            }
            $table->dropColumn(['sip_call_id', 'recording_url']);
        });
    }
};