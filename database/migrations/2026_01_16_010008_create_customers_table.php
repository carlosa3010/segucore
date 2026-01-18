<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Identificación
            $table->string('first_name');
            $table->string('last_name'); // FALTABA ESTE
            $table->string('national_id')->unique();
            $table->string('email')->nullable();
            
            // Ubicación y Contacto
            $table->string('phone_1'); // Renombrado de phone_primary para coincidir con el form
            $table->string('phone_2')->nullable(); // Agregado
            $table->string('address'); // Renombrado de address_monitoring
            $table->string('city')->nullable(); // Agregado
            
            // Seguridad
            $table->string('monitoring_password')->nullable();
            $table->string('duress_password')->nullable();
            
            // Estado
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};