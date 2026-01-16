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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('national_id')->unique();
        $table->string('email')->nullable();
        $table->string('phone_primary');
        $table->string('address_monitoring');
        
        // --- AGREGA ESTAS 2 LÍNEAS DE SEGURIDAD ---
        $table->string('monitoring_password')->nullable(); // "Chocolate"
        $table->string('duress_password')->nullable();     // Coacción
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
