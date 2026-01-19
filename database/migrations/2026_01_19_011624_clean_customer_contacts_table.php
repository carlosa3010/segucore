<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            // Eliminar las columnas viejas que causan el error 1364
            if (Schema::hasColumn('customer_contacts', 'phone_primary')) {
                $table->dropColumn('phone_primary');
            }
            if (Schema::hasColumn('customer_contacts', 'phone_secondary')) {
                $table->dropColumn('phone_secondary');
            }
            
            // Asegurar que 'phone' sea string y nullable por si acaso
            if (Schema::hasColumn('customer_contacts', 'phone')) {
                $table->string('phone')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Revertir sería volver a crear phone_primary, pero no es necesario en este flujo.
    }
};