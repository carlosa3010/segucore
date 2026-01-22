<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            // Relacionamos la cuenta con un plan de servicio
            $table->foreignId('service_plan_id')
                  ->nullable() // Puede ser nulo si es una cuenta de prueba
                  ->after('customer_id')
                  ->constrained('service_plans')
                  ->nullOnDelete(); // Si se borra el plan, la cuenta queda sin plan (no se borra la cuenta)
        });
    }

    public function down(): void
    {
        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->dropForeign(['service_plan_id']);
            $table->dropColumn('service_plan_id');
        });
    }
};