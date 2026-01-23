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
            $table->string('name')->nullable();
            $table->string('model')->nullable(); // <--- ESTA FALTABA
            $table->string('sim_card_number')->nullable();
            
            // Estado y Posición
            $table->string('status')->default('offline')->comment('online, offline, unknown');
            $table->decimal('last_latitude', 10, 7)->nullable();
            $table->decimal('last_longitude', 10, 7)->nullable();
            $table->decimal('speed', 8, 2)->default(0);
            $table->decimal('battery_level', 5, 2)->nullable();
            
            // Configuraciones (JSON)
            $table->json('settings')->nullable(); // Para configuraciones extra
            
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