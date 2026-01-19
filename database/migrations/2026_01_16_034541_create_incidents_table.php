<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            
            // RELACIONES
            $table->foreignId('alarm_event_id')->constrained('alarm_events')->onDelete('cascade');
            $table->foreignId('alarm_account_id')->constrained('alarm_accounts'); // Obligatorio
            $table->foreignId('customer_id')->nullable()->constrained('customers'); // <--- AGREGADO
            $table->foreignId('operator_id')->nullable()->constrained('users');     // <--- AHORA ES NULLABLE (Para cuando se crea auto)
            
            // ESTADO Y GESTIÓN
            $table->string('status')->default('open'); // 'open', 'in_progress', 'closed'
            $table->string('result')->nullable();      // 'false_alarm', 'real', etc.
            $table->text('notes')->nullable();         // Notas de resolución
            
            // TIEMPOS
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};