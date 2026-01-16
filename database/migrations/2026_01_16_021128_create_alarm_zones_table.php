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
    Schema::create('alarm_zones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alarm_account_id')->constrained()->onDelete('cascade');
        
        $table->string('zone_number'); // Ej: "001", "002" (Como viene en el SIA)
        $table->string('name');        // Ej: "Puerta Principal", "Sensor Pasillo"
        $table->string('type')->nullable(); // Ej: Instantánea, Retardada, Fuego
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarm_zones');
    }
};
