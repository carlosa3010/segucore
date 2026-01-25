<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Corregir Alarm Zones (Faltaba partition_id)
        Schema::table('alarm_zones', function (Blueprint $table) {
            if (!Schema::hasColumn('alarm_zones', 'partition_id')) {
                $table->foreignId('partition_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('alarm_partitions')
                      ->cascadeOnDelete();
            }
        });

        // 2. Corregir Customer Contacts (El error del teléfono)
        Schema::table('customer_contacts', function (Blueprint $table) {
            // El formulario envía 'phone', pero la base de datos pedía obligatoriamente 'phone_1'.
            // Hacemos phone_1 nullable para que no de error.
            if (Schema::hasColumn('customer_contacts', 'phone_1')) {
                $table->string('phone_1')->nullable()->change();
            }
        });

        // 3. Corregir Alarm Accounts (Faltaban las notas operativas)
        Schema::table('alarm_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('alarm_accounts', 'permanent_notes')) {
                $table->text('permanent_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('alarm_accounts', 'temporary_notes')) {
                $table->text('temporary_notes')->nullable()->after('permanent_notes');
            }
            if (!Schema::hasColumn('alarm_accounts', 'temporary_notes_until')) {
                $table->dateTime('temporary_notes_until')->nullable()->after('temporary_notes');
            }
        });
    }

    public function down(): void
    {
        // Revertir cambios si es necesario
        Schema::table('alarm_zones', function (Blueprint $table) {
            $table->dropForeign(['partition_id']);
            $table->dropColumn('partition_id');
        });

        Schema::table('alarm_accounts', function (Blueprint $table) {
            $table->dropColumn(['permanent_notes', 'temporary_notes', 'temporary_notes_until']);
        });
    }
};