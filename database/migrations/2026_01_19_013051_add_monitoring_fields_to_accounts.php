<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('alarm_accounts', function (Blueprint $table) {
        $table->timestamp('last_checkin_at')->nullable(); // Último Test Automático recibido
        $table->boolean('is_armed')->default(false);      // Estado Armado/Desarmado
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
};
