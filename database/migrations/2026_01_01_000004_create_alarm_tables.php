<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Cuentas
        Schema::create('alarm_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_plan_id')->nullable()->constrained('service_plans')->nullOnDelete();
            
            $table->string('monitoring_status')->default('disarmed'); // armed, disarmed
            $table->string('branch_name')->nullable();
            $table->text('installation_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_model')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Zonas
        Schema::create('alarm_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->cascadeOnDelete();
            $table->string('zone_number');
            $table->string('name');
            $table->string('type')->nullable(); // Instantanea, Retardada
            $table->timestamps();
        });

        // Códigos SIA
        Schema::create('sia_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description');
            $table->integer('priority')->default(1);
            $table->string('color_hex')->nullable();
            $table->string('sound_alert')->nullable();
            $table->boolean('auto_process')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('sia_codes');
        Schema::dropIfExists('alarm_zones');
        Schema::dropIfExists('alarm_accounts');
    }
};