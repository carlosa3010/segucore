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
        // Relación con el cliente
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        
        // El número de cuenta del panel (Q28252694)
        $table->string('account_number')->unique(); 

        // --- AGREGAR ESTOS CAMPOS FALTANTES ---
        $table->enum('service_status', ['active', 'suspended', 'cancelled'])->default('active');
        $table->timestamp('test_mode_until')->nullable();
        // --------------------------------------

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
