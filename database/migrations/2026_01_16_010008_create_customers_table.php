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
        
        // IDENTIFICACIÓN
        $table->enum('type', ['person', 'company'])->default('person'); // Persona o Empresa
        $table->string('national_id')->unique(); // CI o RIF (J-123456789)
        
        // DATOS LEGALES / PERSONALES
        $table->string('first_name')->nullable(); // Nombre Rep. Legal o Persona
        $table->string('last_name')->nullable();  // Apellido Rep. Legal o Persona
        $table->string('business_name')->nullable(); // Razón Social (Solo empresas)
        
        // CONTACTO ADMINISTRATIVO (FACTURACIÓN)
        $table->string('email')->nullable();
        $table->string('phone_1');
        $table->string('phone_2')->nullable();
        $table->text('address_billing'); // Dirección Fiscal
        $table->string('city');
        
        // SEGURIDAD MAESTRA (Aplica si no se define específica por cuenta)
        $table->string('monitoring_password')->nullable();
        $table->string('duress_password')->nullable();
        
        // ESTADO
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