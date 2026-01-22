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
    Schema::create('device_alerts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('gps_device_id')->constrained()->onDelete('cascade');
        $table->string('type'); // ej: 'overspeed', 'geofence', 'disconnect'
        $table->string('message');
        $table->timestamp('read_at')->nullable(); // Para marcar como leída
        $table->json('data')->nullable(); // Datos extra (lat, lng, velocidad)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_alerts');
    }
};
