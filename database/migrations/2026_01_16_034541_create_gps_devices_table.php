<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_devices', function (Blueprint $table) {
            $table->id();
            $table->string('imei')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            
            // Datos del dispositivo
            $table->string('name')->nullable(); // Nombre descriptivo
            $table->string('plate_number')->nullable(); // <--- AGREGADO
            $table->string('model')->nullable();
            $table->string('sim_card_number')->nullable();
            
            // Configuración
            $table->integer('speed_limit')->default(80); // <--- AGREGADO
            $table->json('settings')->nullable();
            
            // Estado y Posición
            $table->string('status')->default('offline');
            $table->decimal('last_latitude', 10, 7)->nullable();
            $table->decimal('last_longitude', 10, 7)->nullable();
            $table->decimal('speed', 8, 2)->default(0);
            $table->decimal('battery_level', 5, 2)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_devices');
    }
};