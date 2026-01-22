<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // migration
public function up(): void
{
    Schema::create('geofences', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->longText('area'); // WKT format: POLYGON((lat lng, lat lng...))
        $table->integer('traccar_geofence_id')->nullable(); // ID remoto
        $table->timestamps();
    });
    
    // Tabla pivote para asignar geocerca a dispositivo
    Schema::create('geofence_gps_device', function (Blueprint $table) {
        $table->id();
        $table->foreignId('geofence_id')->constrained()->onDelete('cascade');
        $table->foreignId('gps_device_id')->constrained()->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
