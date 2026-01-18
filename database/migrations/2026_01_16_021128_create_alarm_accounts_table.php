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
    Schema::create('alarm_accounts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        $table->string('account_number')->unique(); // Serial SIA (Q28...)

        // UBICACIÓN ESPECÍFICA DEL PANEL (Sucursal)
        $table->string('branch_name')->nullable(); // Ej: "Sucursal Centro", "Casa de Playa"
        $table->text('installation_address')->nullable(); // Dirección física real
        $table->decimal('latitude', 10, 8)->nullable();  // Coordenadas para Mapa
        $table->decimal('longitude', 11, 8)->nullable(); // Coordenadas para Mapa
        
        // ESTADO TÉCNICO
        $table->enum('service_status', ['active', 'suspended', 'cancelled'])->default('active');
        $table->timestamp('test_mode_until')->nullable();
        $table->text('notes')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarm_accounts');
    }
};
