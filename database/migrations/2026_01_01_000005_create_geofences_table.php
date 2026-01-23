<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('gps_device_id')->nullable()->constrained('gps_devices')->cascadeOnDelete();
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('circle');
            $table->json('coordinates'); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('geofences'); }
};