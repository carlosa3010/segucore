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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained();
        
        $table->string('invoice_number')->unique(); // FACT-0001
        $table->decimal('amount', 10, 2);
        $table->date('due_date'); // Fecha vencimiento
        
        $table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled'])->default('unpaid');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
