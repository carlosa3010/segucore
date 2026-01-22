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
    Schema::create('drivers', function (Blueprint $table) {
        $table->id();
        $table->string('full_name');
        $table->string('license_number')->unique(); // Cédula o Licencia
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('photo_path')->nullable(); // Foto del chofer
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });

    // Agregar relación a la tabla de GPS
    Schema::table('gps_devices', function (Blueprint $table) {
        $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete()->after('customer_id');
    });
}

public function down(): void
{
    Schema::table('gps_devices', function (Blueprint $table) {
        $table->dropForeign(['driver_id']);
        $table->dropColumn('driver_id');
    });
    Schema::dropIfExists('drivers');
}