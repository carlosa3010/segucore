<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Usuarios de Panel (La que te da el error actual)
        if (!Schema::hasTable('panel_users')) {
            Schema::create('panel_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->cascadeOnDelete();
                $table->string('user_number', 10); // Ej: 001, 002
                $table->string('name', 100);       // Ej: Gerente
                $table->string('role')->default('user'); // master, user, duress
                $table->timestamps();
            });
        }

        // 2. Tabla de Horarios (Faltaba también)
        if (!Schema::hasTable('account_schedules')) {
            Schema::create('account_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->cascadeOnDelete();
                $table->enum('type', ['weekly', 'temporary'])->default('weekly');
                $table->integer('day_of_week')->default(0); // 0-7
                $table->time('open_time')->nullable();
                $table->time('close_time')->nullable();
                $table->integer('tolerance_minutes')->default(30);
                $table->string('reason')->nullable(); // Para horarios temporales
                $table->dateTime('valid_until')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tabla de Bitácora de Cuenta (Faltaba también)
        if (!Schema::hasTable('account_logs')) {
            Schema::create('account_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type')->default('note'); // note, call, alert
                $table->text('content');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('account_logs');
        Schema::dropIfExists('account_schedules');
        Schema::dropIfExists('panel_users');
    }
};