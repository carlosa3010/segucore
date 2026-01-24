<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SiaCode;
use App\Models\Customer;
use App\Models\AlarmAccount;
use App\Models\AlarmZone;
use App\Models\AlarmPartition; // <-- Nuevo
use App\Models\GpsDevice;
use App\Models\ServicePlan;
use App\Models\Driver;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'admin@segusmart24.com'],
            [
                'name' => 'Carlos Morales',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 2. PLAN
        $plan = ServicePlan::firstOrCreate(
            ['name' => 'Plan Comercial Avanzado'],
            [
                'price' => 45.00,
                'billing_cycle' => 'monthly',
                'features' => json_encode(['monitoreo']),
                'description' => 'Seguridad integral.'
            ]
        );

        // 3. SIA CODES (Resumido para que corra rápido, agrega el resto si quieres)
        $codes = [
            ['code' => 'BA', 'desc' => 'Burglary Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'FA', 'desc' => 'Fire Alarm', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'PA', 'desc' => 'Panic Alarm', 'prio' => 5, 'color' => '#8b0000'],
            ['code' => 'OP', 'desc' => 'Opening', 'prio' => 1, 'color' => '#00bfff'],
            ['code' => 'CL', 'desc' => 'Closing', 'prio' => 1, 'color' => '#808080'],
        ];
        foreach ($codes as $c) {
            SiaCode::updateOrCreate(['code' => $c['code']], [
                'description' => $c['desc'],
                'priority' => $c['prio'],
                'color_hex' => $c['color']
            ]);
        }

        // 4. CLIENTE
        $customer = Customer::updateOrCreate(
            ['dni_cif' => 'J-123456789'], 
            [
                'type' => 'company', 
                'business_name' => 'Inversiones La Esperanza C.A.', 
                'first_name' => 'Pedro', 
                'last_name' => 'Pérez',
                'email' => 'admin@laesperanza.com',
                'phone_1' => '0414-5555555',
                'address' => 'Av. 20 con Calle 30, Barquisimeto',
                'city' => 'Barquisimeto',
                'is_active' => true,
                'status' => 'active' // <--- COLUMNA CRÍTICA PARA TUS VISTAS
            ]
        );

        // 5. ALARMA
        $account = AlarmAccount::updateOrCreate(
            ['account_number' => 'Q28252694'], 
            [
                'customer_id' => $customer->id,
                'service_plan_id' => $plan->id,
                'service_status' => 'active', 
                'monitoring_status' => 'armed',
                'branch_name' => 'Sucursal Centro', 
                'installation_address' => 'Calle 30 entre 19 y 20',
                'latitude' => 10.065000,
                'longitude' => -69.335000,
                'device_model' => 'DSC PowerSeries Neo',
                'is_active' => true
            ]
        );

        // ZONAS
        AlarmZone::firstOrCreate(
            ['alarm_account_id' => $account->id, 'zone_number' => '001'],
            ['name' => 'Entrada Principal', 'type' => 'Retardada']
        );

        // PARTICIONES (ESTO FALTABA y rompe "Gestionar Cuentas")
        AlarmPartition::firstOrCreate(
            ['alarm_account_id' => $account->id, 'partition_number' => 1],
            ['name' => 'Partición General', 'status' => 'armed']
        );

        // 6. CONDUCTOR Y GPS
        $driver = Driver::create([
            'customer_id' => $customer->id,
            'first_name' => 'Juan',
            'last_name' => 'Conductor',
            'full_name' => 'Juan Conductor', // <--- COLUMNA CRÍTICA PARA EL ORDENAMIENTO
            'phone' => '0412-0000000',
            'status' => 'active', // <--- COLUMNA CRÍTICA PARA EL FILTRO WHERE
            'is_active' => true
        ]);

        GpsDevice::updateOrCreate(
            ['imei' => '864035052312569'],
            [
                'customer_id' => $customer->id,
                'driver_id' => $driver->id,
                'name' => 'Camión Ford Cargo',
                'plate_number' => 'A12BC34',
                'model' => 'Coban 303G',
                'sim_card_number' => '0412-9876543',
                'status' => 'online',
                'last_latitude' => 10.070000,
                'last_longitude' => -69.330000,
                'speed' => 60,
                'is_active' => true
            ]
        );

        // 7. USUARIO CLIENTE
        if (!User::where('email', 'cliente@laesperanza.com')->exists()) {
            User::create([
                'name' => 'Pedro Pérez',
                'email' => 'cliente@laesperanza.com',
                'password' => Hash::make('cliente123'),
                'role' => 'client',
                'customer_id' => $customer->id,
                'is_active' => true,
            ]);
        }
    }
}