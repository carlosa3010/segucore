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
    Schema::create('customer_contacts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        
        $table->string('name');             // "Pedro Pérez"
        $table->string('relationship');     // "Dueño", "Vecino", "Policía"
        $table->string('phone_primary');    
        $table->string('phone_secondary')->nullable();
        
        // Lógica de Protocolo
        $table->integer('call_order')->default(1); // 1 = Primero, 2 = Segundo...
        $table->boolean('has_keys')->default(false); // ¿Tiene llaves del local?
        $table->string('secret_keyword')->nullable(); // Palabra clave individual
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_contacts');
    }
};
