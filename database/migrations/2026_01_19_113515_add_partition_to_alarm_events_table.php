<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_events', function (Blueprint $table) {
            // Agregamos la columna 'partition' después de 'zone'
            $table->string('partition', 10)->nullable()->default('0')->after('zone');
        });
    }

    public function down(): void
    {
        Schema::table('alarm_events', function (Blueprint $table) {
            $table->dropColumn('partition');
        });
    }
};