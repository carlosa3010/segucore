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
    Schema::table('sia_codes', function (Blueprint $table) {
        $table->text('procedure_instructions')->nullable()->after('sound_alert');
        $table->boolean('requires_schedule_check')->default(false)->after('procedure_instructions');
        $table->integer('schedule_grace_minutes')->default(30)->after('requires_schedule_check');
        $table->string('schedule_violation_action')->default('none')->after('schedule_grace_minutes');
    });
}

public function down(): void
{
    Schema::table('sia_codes', function (Blueprint $table) {
        $table->dropColumn([
            'procedure_instructions', 
            'requires_schedule_check', 
            'schedule_grace_minutes', 
            'schedule_violation_action'
        ]);
    });
}
};