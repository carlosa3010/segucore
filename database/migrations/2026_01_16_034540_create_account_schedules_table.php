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
    Schema::create('account_schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->onDelete('cascade');
        
        $table->integer('day_of_week'); // 0=Domingo, 1=Lunes, ... 6=Sábado
        $table->time('open_time');      // Hora apertura esperada
        $table->time('close_time');     // Hora cierre esperado
        $table->integer('tolerance_minutes')->default(30); // Margen de error
        
        $table->boolean('is_holiday')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_schedules');
    }
};
