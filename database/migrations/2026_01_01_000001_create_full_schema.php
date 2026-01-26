<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. CONFIGURACIÓN Y USUARIOS
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('operator')->after('email');
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
            });
        }

        // 2. MÓDULO COMERCIAL
        if (!Schema::hasTable('service_plans')) {
            Schema::create('service_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('price', 10, 2)->default(0);
                $table->string('billing_cycle')->default('monthly');
                $table->json('features')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['person', 'company'])->default('person');
                $table->string('national_id')->unique(); // dni_cif renombrado
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('business_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone_1')->nullable();
                $table->string('phone_2')->nullable();
                $table->text('address')->nullable();
                $table->text('address_billing')->nullable();
                $table->string('city')->nullable();
                $table->string('monitoring_password')->nullable();
                $table->string('duress_password')->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('active'); 
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'customer_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('customer_contacts')) {
            Schema::create('customer_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('name');
                $table->string('relation')->nullable(); 
                $table->string('relationship')->nullable(); // Alias para compatibilidad
                $table->string('phone_1');
                $table->string('phone')->nullable(); // Alias
                $table->string('phone_2')->nullable();
                $table->boolean('has_keys')->default(false);
                $table->integer('priority')->default(1);
                $table->timestamps();
            });
        }

        // 3. FACTURACIÓN
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('invoice_number')->unique();
                $table->date('issue_date');
                $table->date('due_date');
                $table->decimal('total', 10, 2);
                $table->string('status')->default('unpaid');
                
                // ✅ AGREGA ESTA LÍNEA:
                $table->json('details')->nullable(); 

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->date('payment_date');
                $table->string('method')->nullable();
                $table->string('reference')->nullable();
                $table->timestamps();
            });
        }

        // 4. FLOTAS
        if (!Schema::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('full_name')->nullable(); 
                $table->string('dni')->nullable();
                $table->string('license_number')->nullable();
                $table->string('phone')->nullable();
                $table->string('status')->default('active'); 
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gps_devices')) {
            Schema::create('gps_devices', function (Blueprint $table) {
                $table->id();
                $table->string('imei')->unique();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
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
        }

        if (!Schema::hasTable('geofences')) {
            Schema::create('geofences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                //$table->foreignId('gps_device_id')->nullable()->constrained('gps_devices')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('type')->default('circle');
                $table->json('coordinates');
                $table->boolean('alert_on_enter')->default(true);
                $table->boolean('alert_on_exit')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ✅ AGREGA ESTO: Nueva tabla pivote para la relación Muchos a Muchos
        if (!Schema::hasTable('geofence_gps_device')) {
            Schema::create('geofence_gps_device', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gps_device_id')->constrained('gps_devices')->cascadeOnDelete();
                $table->foreignId('geofence_id')->constrained('geofences')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('device_alerts')) {
            Schema::create('device_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gps_device_id')->constrained('gps_devices')->cascadeOnDelete();
                $table->string('type');
                $table->string('message');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamp('occurred_at')->useCurrent();
                $table->timestamp('read_at')->nullable();
                $table->boolean('read')->default(false);
                $table->timestamps();
            });
        }

        // 5. ALARMAS
        if (!Schema::hasTable('alarm_accounts')) {
            Schema::create('alarm_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('account_number')->unique();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('service_plan_id')->nullable()->constrained('service_plans')->nullOnDelete();
                $table->string('service_status')->default('active');
                $table->string('monitoring_status')->default('disarmed');
                $table->string('branch_name')->nullable();
                $table->text('installation_address')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('device_model')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('test_mode_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('alarm_partitions')) {
            Schema::create('alarm_partitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->cascadeOnDelete();
                $table->integer('partition_number');
                $table->string('name')->nullable();
                $table->string('status')->default('disarmed');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('alarm_zones')) {
            Schema::create('alarm_zones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->cascadeOnDelete();
                $table->string('zone_number');
                $table->string('name');
                $table->string('type')->nullable();
                $table->string('sensor_type')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sia_codes')) {
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

        if (!Schema::hasTable('alarm_events')) {
            Schema::create('alarm_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alarm_account_id')->constrained('alarm_accounts')->cascadeOnDelete();
                $table->string('event_code')->nullable();
                $table->string('code')->nullable();
                $table->string('description')->nullable();
                $table->string('zone')->nullable();
                $table->string('partition')->nullable();
                $table->text('raw_data')->nullable();
                $table->timestamp('received_at')->useCurrent();
                $table->boolean('processed')->default(false);
                $table->timestamps();
            });
        }

        // 6. SEGURIDAD FÍSICA
        if (!Schema::hasTable('guards')) {
            Schema::create('guards', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('dni')->unique();
                $table->string('phone')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('patrols')) {
            Schema::create('patrols', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('customers');
                $table->foreignId('gps_device_id')->nullable()->constrained('gps_devices')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('patrol_rounds')) {
            Schema::create('patrol_rounds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patrol_id')->constrained('patrols');
                $table->foreignId('guard_id')->nullable()->constrained('guards');
                $table->timestamp('start_time');
                $table->timestamp('end_time')->nullable();
                $table->string('status')->default('in_progress');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. INCIDENTES
        if (!Schema::hasTable('incident_resolutions')) {
            Schema::create('incident_resolutions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('incident_hold_reasons')) {
            Schema::create('incident_hold_reasons', function (Blueprint $table) {
                $table->id();
                $table->string('reason');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('incidents')) {
            Schema::create('incidents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('alarm_account_id')->nullable()->constrained('alarm_accounts')->nullOnDelete();
                $table->foreignId('gps_device_id')->nullable()->constrained('gps_devices')->nullOnDelete();
                
                // CORRECCIÓN CRÍTICA: Se agrega la columna alarm_event_id
                $table->foreignId('alarm_event_id')->nullable()->constrained('alarm_events')->nullOnDelete();
                
                $table->foreignId('operator_id')->nullable()->constrained('users'); 
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->string('priority')->default('medium');
                $table->string('status')->default('open');
                $table->timestamp('occurred_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('incident_logs')) {
            Schema::create('incident_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->text('action'); 
                $table->string('type')->default('comment');
                $table->timestamps();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('incident_logs');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('incident_hold_reasons');
        Schema::dropIfExists('incident_resolutions');
        Schema::dropIfExists('patrol_rounds');
        Schema::dropIfExists('patrols');
        Schema::dropIfExists('guards');
        Schema::dropIfExists('alarm_events');
        Schema::dropIfExists('sia_codes');
        Schema::dropIfExists('alarm_zones');
        Schema::dropIfExists('alarm_partitions');
        Schema::dropIfExists('alarm_accounts');
        Schema::dropIfExists('device_alerts');
        Schema::dropIfExists('geofences');
        Schema::dropIfExists('gps_devices');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('service_plans');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('settings');
        Schema::enableForeignKeyConstraints();
    }
};