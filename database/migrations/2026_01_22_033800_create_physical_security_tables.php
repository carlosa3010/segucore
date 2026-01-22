<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. PATRULLAS (Unidades móviles)
        Schema::create('patrols', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Móvil Alfa-1"
            $table->string('vehicle_type')->default('car'); // car, motorcycle, foot (a pie)
            $table->string('plate_number')->nullable();
            
            // Vinculación con GPS existente (opcional, una patrulla puede ser solo el guardia con su app)
            $table->foreignId('gps_device_id')->nullable()->constrained('gps_devices')->nullOnDelete();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. GUARDIAS (Personal)
        Schema::create('guards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Para login en App
            $table->string('full_name');
            $table->string('badge_number')->unique(); // Número de placa/carnet
            $table->string('phone')->nullable();
            
            // Asignación actual (puede ser nulo si está en descanso)
            $table->foreignId('current_patrol_id')->nullable()->constrained('patrols')->nullOnDelete();
            
            $table->boolean('on_duty')->default(false); // ¿Está en turno?
            $table->timestamps();
        });

        // 3. DEFINICIÓN DE RONDAS
        Schema::create('patrol_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Ronda Perimetral Norte"
            $table->text('description')->nullable();
            $table->integer('interval_minutes')->default(60); // Frecuencia esperada
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Puntos de control de la ronda (Usamos las Geocercas que ya creamos)
        Schema::create('patrol_round_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_round_id')->constrained()->onDelete('cascade');
            $table->foreignId('geofence_id')->constrained()->onDelete('cascade');
            $table->integer('order_index'); // 1, 2, 3... secuencia
            $table->timestamps();
        });
        
        // 4. EJECUCIÓN DE RONDAS (Historial)
        Schema::create('round_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guard_id')->constrained();
            $table->foreignId('patrol_round_id')->constrained();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'incomplete'])->default('in_progress');
            $table->timestamps();
        });

        // Registro de paso por punto
        Schema::create('round_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_execution_id')->constrained();
            $table->foreignId('geofence_id')->constrained();
            $table->dateTime('visited_at');
            $table->boolean('is_manual_check')->default(false); // Si GPS falla, check manual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_execution_logs');
        Schema::dropIfExists('round_executions');
        Schema::dropIfExists('patrol_round_checkpoints');
        Schema::dropIfExists('patrol_rounds');
        Schema::dropIfExists('guards');
        Schema::dropIfExists('patrols');
    }
};