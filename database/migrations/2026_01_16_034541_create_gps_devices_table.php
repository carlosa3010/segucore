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
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            
            $table->string('name'); // Nombre/Alias (Ej: Camión Ford)
            $table->string('imei')->unique(); // <--- AHORA SE LLAMA IMEI
            
            $table->string('phone_number')->nullable();
            $table->string('sim_card_id')->nullable();
            $table->string('device_model');
            $table->string('plate_number')->nullable();
            $table->enum('vehicle_type', ['car', 'truck', 'motorcycle', 'person'])->default('car');
            $table->string('driver_name')->nullable();
            $table->date('installation_date')->nullable();
            $table->enum('subscription_status', ['active', 'suspended'])->default('active');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_devices');
    }
};