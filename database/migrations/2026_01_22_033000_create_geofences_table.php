<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('gps_device_id')->nullable()->constrained('gps_devices')->onDelete('cascade');
            
            // Datos Geocerca
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('circle'); // circle, polygon, polyline
            
            // Coordenadas (Guardamos el área en JSON para flexibilidad)
            // Ej: {"lat": 10.1, "lng": -69.2, "radius": 500}
            $table->json('coordinates'); 
            
            // Reglas
            $table->boolean('alert_on_enter')->default(true);
            $table->boolean('alert_on_exit')->default(true);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};