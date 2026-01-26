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
        Schema::table('incidents', function (Blueprint $table) {
            // Agregamos campo para el resultado del cierre (Ej: 'FALSE_ALARM', 'POLICE')
            if (!Schema::hasColumn('incidents', 'result')) {
                $table->string('result')->nullable()->after('status');
            }

            // Agregamos campo para las notas de cierre (El informe final)
            if (!Schema::hasColumn('incidents', 'notes')) {
                $table->text('notes')->nullable()->after('result');
            }
            
            // Agregamos closing_notes por si acaso se usa en otra parte del sistema
            if (!Schema::hasColumn('incidents', 'closing_notes')) {
                $table->text('closing_notes')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['result', 'notes', 'closing_notes']);
        });
    }
};