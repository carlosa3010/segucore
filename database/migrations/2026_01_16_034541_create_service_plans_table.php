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
    Schema::create('service_plans', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // "Plan Residencial Básico"
        $table->decimal('price', 10, 2); // 25.00
        $table->string('currency')->default('USD');
        $table->integer('billing_cycle_days')->default(30); // Cada cuánto se cobra
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
