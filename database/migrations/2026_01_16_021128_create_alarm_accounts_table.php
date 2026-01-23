<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alarm_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique()->comment('Número de abonado');
            
            // Relaciones
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('service_plan_id')->nullable()->constrained('service_plans')->onDelete('set null');
            
            // Estados del servicio
            $table->string('monitoring_status')->default('disarmed')->comment('armed, disarmed, alarm, offline');
            $table->string('service_status')->default('active')->comment('active, suspended');
            
            // Detalles de Instalación
            $table->string('branch_name')->nullable()->comment('Nombre de sucursal o casa');
            $table->text('installation_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            // Detalles Técnicos
            $table->string('device_model')->nullable(); // Ej: DSC PowerSeries
            $table->text('notes')->nullable();
            $table->timestamp('test_mode_until')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alarm_accounts');
    }
};