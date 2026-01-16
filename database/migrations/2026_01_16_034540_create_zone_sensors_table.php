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
    Schema::create('zone_sensors', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alarm_zone_id')->constrained()->onDelete('cascade');
        
        $table->string('sensor_type'); // PIR, Magnético, Humo, Vidrio
        $table->string('model')->nullable(); // "DSC LC-100"
        $table->string('serial_number')->nullable();
        $table->date('battery_installed_at')->nullable(); // Mantenimiento
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_sensors');
    }
};
