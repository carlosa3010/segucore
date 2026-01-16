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
    Schema::create('sia_codes', function (Blueprint $table) {
        $table->id();
        $table->string('code', 10)->unique(); // BA1, FA1
        $table->string('description');        // "Robo", "Fuego"
        
        // UX Operador
        $table->integer('priority')->default(1); // 1=Info, 5=Pánico Crítico
        $table->string('color_hex', 7)->default('#808080'); // Color en pantalla
        $table->string('sound_alert')->nullable(); // 'siren.mp3', 'ping.mp3'
        
        $table->text('operator_instruction')->nullable(); // "Llamar Policía Inmediato"
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sia_codes');
    }
};
