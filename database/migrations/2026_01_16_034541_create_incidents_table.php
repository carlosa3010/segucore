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
    Schema::create('incidents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alarm_account_id')->constrained('alarm_accounts');
        $table->foreignId('alarm_event_id')->nullable(); // Evento disparador
        
        $table->foreignId('operator_id')->constrained('users'); // Quién lo trabaja
        
        $table->enum('status', ['open', 'monitoring', 'police_dispatched', 'closed'])->default('open');
        $table->string('resolution_code')->nullable(); // FALSA_ALARMA, REAL, TEST
        
        $table->timestamp('started_at')->useCurrent();
        $table->timestamp('closed_at')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
