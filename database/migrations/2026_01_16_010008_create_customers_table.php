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
            
            // TIPO Y DATOS PRINCIPALES
            $table->enum('type', ['person', 'company'])->default('person');
            $table->string('dni_cif')->unique()->comment('Cédula o RIF'); // <--- ESTA ES LA CLAVE
            
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('business_name')->nullable();
            
            // CONTACTO
            $table->string('email')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            
            // DIRECCIÓN
            $table->text('address')->nullable(); // Dirección fiscal/principal
            $table->text('address_billing')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Venezuela');
            
            // DATOS DE MONITOREO
            $table->string('monitoring_password')->nullable()->comment('Palabra clave verbal');
            
            // ESTADO
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};