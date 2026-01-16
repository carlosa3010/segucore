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
        Schema::create('alarm_events', function (Blueprint $table) {
            $table->id();
            
            // IDENTIFICACIÓN DEL CLIENTE
            // Indexado para búsquedas rápidas ("Dime todas las alarmas del cliente X")
            $table->string('account_number', 20)->index(); 

            // DETALLES DEL EVENTO SIA
            $table->string('event_code', 10);      // Ej: BA1 (Robo), RP000 (Test)
            $table->string('event_type', 10)->nullable(); // Ej: ri0 (Restore/New)
            $table->string('zone', 20)->nullable();       // Ej: Zona 1, Usuario 5
            
            // AUDITORÍA TÉCNICA
            $table->string('ip_address', 45)->nullable(); // IP del Receiver (IPv4/IPv6)
            $table->text('raw_data')->nullable();         // La trama original exacta (Evidencia forense)
            
            // GESTIÓN DE OPERADORES (Vital para el CRM)
            $table->boolean('processed')->default(false)->index(); // ¿Ya se atendió?
            $table->timestamp('processed_at')->nullable();         // ¿Cuándo se atendió?
            $table->unsignedBigInteger('processed_by')->nullable(); // ¿Qué operador la cerró? (Futuro)
            
            // FECHAS
            // received_at es vital porque created_at puede variar milisegundos
            $table->timestamp('received_at')->useCurrent(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarm_events');
    }
};
