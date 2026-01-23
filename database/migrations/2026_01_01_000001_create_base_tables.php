<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Planes de Servicio
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('billing_cycle')->default('monthly');
            $table->json('features')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Clientes
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['person', 'company'])->default('person');
            $table->string('dni_cif')->unique(); // Cédula o RIF
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('monitoring_password')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // 3. Añadir customer_id a users (Relación)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            $table->string('role')->default('operator')->after('email'); // admin, client, operator
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'role', 'is_active']);
        });
        Schema::dropIfExists('customers');
        Schema::dropIfExists('service_plans');
    }
};