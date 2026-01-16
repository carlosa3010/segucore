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
    Schema::create('panel_users', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->onDelete('cascade');
        
        $table->string('user_slot', 5); // Ej: "001", "040" (Número en el teclado)
        $table->string('name');         // "Gerente Juan"
        $table->string('role')->nullable(); // "Apertura", "Maestro", "Limpieza"
        
        $table->boolean('is_duress')->default(false); // ¿Es código de coacción?
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_users');
    }
};
