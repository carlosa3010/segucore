<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            // 1. Solucionar error SQL (Si no existe phone, lo crea)
            if (!Schema::hasColumn('customer_contacts', 'phone')) {
                $table->string('phone')->after('relationship');
            }

            // 2. Agregar campo de Prioridad
            if (!Schema::hasColumn('customer_contacts', 'priority')) {
                $table->integer('priority')->default(1)->after('customer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->dropColumn(['phone', 'priority']);
        });
    }
};
