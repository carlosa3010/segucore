<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar Notas y Coordenadas a la Cuenta
        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->text('permanent_notes')->nullable(); // Notas Fijas (Ej: "Perro bravo en patio")
            $table->text('temporary_notes')->nullable(); // Notas Temporales (Ej: "Cliente de viaje")
            $table->dateTime('temporary_notes_until')->nullable(); // Hasta cuándo es válida la nota
        });

        // 2. Tabla de Particiones (Áreas del sistema de alarma)
        Schema::create('alarm_partitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alarm_account_id')->constrained()->onDelete('cascade');
            $table->integer('partition_number'); // 1, 2, 3...
            $table->string('name'); // "Casa Principal", "Anexo", "Garaje"
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alarm_partitions');
        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->dropColumn(['permanent_notes', 'temporary_notes', 'temporary_notes_until']);
        });
    }
};
