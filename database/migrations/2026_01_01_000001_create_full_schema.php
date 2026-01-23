<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Planes
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
            $table->string('dni_cif')->unique();
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

        // 3. Modificar Usuarios (Agregar customer_id)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            $table->string('role')->default('operator')->after('email');
            $table->boolean('is_active')->default(true);
        });

        // 4. Conductores
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('dni')->nullable();
            $table->string('license_number')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. GPS
        Schema::create('gps_devices', function (Blueprint $table) {
            $table->id();
            $table->string('imei')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('model')->nullable();
            $table->string('sim_card_number')->nullable();
            $table->integer('speed_limit')->default(80);
            $table->string('status')->default('offline');
            $table->decimal('last_latitude', 10, 7)->nullable();
            $table->decimal('last_longitude', 10, 7)->nullable();
            $table->decimal('speed', 8, 2)->default(0);
            $table->decimal('battery_level', 5, 2)->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Alarmas
        Schema::create('alarm_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_plan_id')->nullable()->constrained('service_plans')->nullOnDelete();
            $table->string('monitoring_status')->default('disarmed');
            $table->string('branch_name')->nullable();
            $table->text('installation_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_model')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Geocercas
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('gps_device_id')->nullable()->constrained('gps_devices')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('circle');
            $table->json('coordinates');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. Códigos SIA
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
        // Orden inverso para evitar errores de FK
        Schema::dropIfExists('sia_codes');
        Schema::dropIfExists('geofences');
        Schema::dropIfExists('alarm_accounts');
        Schema::dropIfExists('gps_devices');
        Schema::dropIfExists('drivers');

        if (Schema::hasColumn('users', 'customer_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn(['customer_id', 'role', 'is_active']);
            });
        }

        Schema::dropIfExists('customers');
        Schema::dropIfExists('service_plans');
    }
};