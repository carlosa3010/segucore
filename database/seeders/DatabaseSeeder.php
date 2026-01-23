<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SiaCode;
use App\Models\Customer;
use App\Models\AlarmAccount;
use App\Models\AlarmZone;
use App\Models\GpsDevice;
use App\Models\ServicePlan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------
        // 1. USUARIOS ADMINISTRATIVOS
        // ---------------------------------------------------------
        if (!User::where('email', 'admin@segusmart24.com')->exists()) {
            User::create([
                'name' => 'Carlos Morales',
                'email' => 'admin@segusmart24.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
            $this->command->info('✅ Usuario Admin creado: admin@segusmart24.com');
        }

        // ---------------------------------------------------------
        // 2. PLAN DE SERVICIO (Requisito para crear cuentas)
        // ---------------------------------------------------------
        $plan = ServicePlan::firstOrCreate(
            ['name' => 'Plan Comercial Avanzado'],
            [
                'price' => 45.00,
                'billing_cycle' => 'monthly',
                'features' => json_encode(['monitoreo', 'app_movil', 'reportes_email', 'video_verificacion']),
                'description' => 'Seguridad integral para negocios.'
            ]
        );

        // ---------------------------------------------------------
        // 3. DICCIONARIO SIA (Carga Masiva)
        // ---------------------------------------------------------
        $codes = [
            // Pánico y Emergencia
            ['code' => 'FA', 'desc' => 'Alarma de Fuego', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'PA', 'desc' => 'Pánico Silencioso', 'prio' => 5, 'color' => '#8b0000', 'sound' => 'panic.mp3'],
            ['code' => 'AA', 'desc' => 'Pánico Audible', 'prio' => 5, 'color' => '#8b0000', 'sound' => 'panic.mp3'],
            ['code' => 'MA', 'desc' => 'Emergencia Médica', 'prio' => 5, 'color' => '#0000ff', 'sound' => 'medical.mp3'],
            
            // Robos
            ['code' => 'BA', 'desc' => 'Alarma de Robo', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'BR', 'desc' => 'Restauración Robo', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'TA', 'desc' => 'Sabotaje (Tamper)', 'prio' => 4, 'color' => '#ff8c00', 'sound' => 'tamper.mp3'],
            
            // Aperturas y Cierres
            ['code' => 'OP', 'desc' => 'Apertura (Desarmado)', 'prio' => 1, 'color' => '#00bfff', 'sound' => null],
            ['code' => 'CL', 'desc' => 'Cierre (Armado)', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'OA', 'desc' => 'Apertura Temprana', 'prio' => 2, 'color' => '#ffa500', 'sound' => null],
            ['code' => 'CA', 'desc' => 'Cierre Tardío', 'prio' => 2, 'color' => '#ffa500', 'sound' => null],

            // Fallos Técnicos
            ['code' => 'AT', 'desc' => 'Fallo AC (Corriente)', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'fault.mp3'],
            ['code' => 'AR', 'desc' => 'Restauración AC', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'YT', 'desc' => 'Batería Baja Sistema', 'prio' => 3, 'color' => '#ffd700', 'sound' => 'fault.mp3'],
            ['code' => 'YR', 'desc' => 'Restauración Batería', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'RP', 'desc' => 'Test Automático', 'prio' => 0, 'color' => '#d3d3d3', 'sound' => null],
        ];

        foreach ($codes as $c) {
            SiaCode::updateOrCreate(['code' => $c['code']], [
                'description' => $c['desc'],
                'priority' => $c['prio'],
                'color_hex' => $c['color'],
                'sound_alert' => $c['sound'] ?? null
            ]);
        }
        $this->command->info('✅ Diccionario SIA cargado.');

        // ---------------------------------------------------------
        // 4. CLIENTE ESPECÍFICO
        // ---------------------------------------------------------
        $customer = Customer::updateOrCreate(
            ['dni_cif' => 'J-123456789'], // Usamos dni_cif como clave única
            [
                'type' => 'company', 
                'business_name' => 'Inversiones La Esperanza C.A.', 
                'first_name' => 'Pedro', 
                'last_name' => 'Pérez',
                'email' => 'admin@laesperanza.com',
                'phone_1' => '0251-5555555',
                'phone_2' => '0414-5555555',
                'address' => 'Av. 20 con Calle 30, Torre Empresarial, Piso 1',
                'city' => 'Barquisimeto',
                'is_active' => true,
                'notes' => 'Cliente VIP. Contactar al Sr. Pedro para temas de facturación.'
            ]
        );
        $this->command->info('✅ Cliente creado: ' . $customer->business_name);

        // ---------------------------------------------------------
        // 5. CUENTA DE ALARMA (Q28252694)
        // ---------------------------------------------------------
        $account = AlarmAccount::updateOrCreate(
            ['account_number' => 'Q28252694'], 
            [
                'customer_id' => $customer->id,
                'service_plan_id' => $plan->id,
                'monitoring_status' => 'armed', // Estado inicial
                'branch_name' => 'Sucursal Centro', 
                'installation_address' => 'Calle 30 entre 19 y 20, Local 4',
                'latitude' => 10.065000,
                'longitude' => -69.335000,
                'device_model' => 'DSC PowerSeries Neo',
                'notes' => 'Comunicador IP principal, GPRS respaldo.',
                'is_active' => true
            ]
        );
        $this->command->info('✅ Cuenta Alarma creada: Q28252694');

        // Zonas de la Alarma
        $zones = [
            ['001', 'Magnético Entrada Ppal', 'Retardada'],
            ['002', 'PIR Área Caja', 'Instantánea'],
            ['003', 'PIR Almacén', 'Seguimiento'],
            ['004', 'Pánico Botón Gerencia', '24 Horas'],
            ['005', 'Sensor Humo Cocina', 'Fuego'],
        ];

        foreach ($zones as $zone) {
            AlarmZone::firstOrCreate(
                ['alarm_account_id' => $account->id, 'zone_number' => $zone[0]],
                ['name' => $zone[1], 'type' => $zone[2]]
            );
        }

        // ---------------------------------------------------------
        // 6. DISPOSITIVO GPS (864035052312569)
        // ---------------------------------------------------------
        GpsDevice::updateOrCreate(
            ['imei' => '864035052312569'],
            [
                'customer_id' => $customer->id,
                'name' => 'Camión Ford Cargo (Placa A12BC34)',
                'model' => 'Coban 303G',
                'sim_card_number' => '0412-9876543',
                'status' => 'online',
                'last_latitude' => 10.070000, // Un poco más al norte para que se vean separados en el mapa
                'last_longitude' => -69.330000,
                'speed' => 60,
                'is_active' => true
            ]
        );
        $this->command->info('✅ Dispositivo GPS creado: 864035052312569');

        // ---------------------------------------------------------
        // 7. USUARIO CLIENTE (Para probar el Panel Cliente)
        // ---------------------------------------------------------
        if (!User::where('email', 'cliente@laesperanza.com')->exists()) {
            User::create([
                'name' => 'Pedro Pérez (Cliente)',
                'email' => 'cliente@laesperanza.com',
                'password' => Hash::make('cliente123'),
                'role' => 'client',
                'customer_id' => $customer->id, // <--- VINCULACIÓN CLAVE
                'is_active' => true,
            ]);
            $this->command->info('✅ Usuario Cliente creado: cliente@laesperanza.com');
        }
    }
}