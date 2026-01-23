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
    Schema::table('users', function (Blueprint $table) {
        // Agregamos la llave foránea nullable (porque los admins no tienen cliente)
        $table->foreignId('customer_id')
              ->nullable()
              ->after('id') // O donde prefieras
              ->constrained('customers')
              ->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['customer_id']);
        $table->dropColumn('customer_id');
    });
}
};
