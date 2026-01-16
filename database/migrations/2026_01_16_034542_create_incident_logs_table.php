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
    Schema::create('incident_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('incident_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained(); // Operador
        
        $table->string('action_type'); // NOTE, CALL_OUT, POLICE, SYSTEM
        $table->text('description');   // "Cliente no contesta, se llama al vecino"
        
        // Integración SIP / Grabaciones
        $table->string('sip_call_id')->nullable(); // ID de la PBX
        $table->string('recording_url')->nullable(); // Link al audio
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_logs');
    }
};
