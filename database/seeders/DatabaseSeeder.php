<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SiaCode;
use App\Models\Customer;
use App\Models\AlarmAccount;
use App\Models\AlarmZone;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. USUARIO SUPER ADMIN
        if (!User::where('email', 'admin@segusmart.com')->exists()) {
            User::create([
                'name' => 'Carlos Morales',
                'email' => 'admin@segusmart.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);
            $this->command->info('✅ Usuario Admin creado: admin@segusmart.com');
        }

        // 2. DICCIONARIO SIA COMPLETO (Estándar SIA-DCS)
        // Prio: 5=Pánico/Fuego, 4=Robo, 3=Técnico Urgente, 2=Técnico, 1=Info/Aperturas, 0=Test
        $codes = [
            // --- ALARMAS DE EMERGENCIA (Prioridad 5) ---
            ['code' => 'FA', 'desc' => 'Alarma de Fuego', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'KA', 'desc' => 'Alarma de Calor', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'KS', 'desc' => 'Alarma de Humo', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'MA', 'desc' => 'Emergencia Médica', 'prio' => 5, 'color' => '#0000ff', 'sound' => 'panic.mp3'],
            ['code' => 'PA', 'desc' => 'Pánico Silencioso', 'prio' => 5, 'color' => '#8b0000', 'sound' => 'panic.mp3'],
            ['code' => 'AA', 'desc' => 'Pánico Audible', 'prio' => 5, 'color' => '#8b0000', 'sound' => 'panic.mp3'],
            ['code' => 'HA', 'desc' => 'Holdup (Atraco/Coacción)', 'prio' => 5, 'color' => '#8b0000', 'sound' => 'panic.mp3'],
            ['code' => 'QA', 'desc' => 'Emergencia Auxiliar', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'warning.mp3'],
            
            // --- ALARMAS DE ROBO (Prioridad 4) ---
            ['code' => 'BA', 'desc' => 'Alarma de Robo', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'UA', 'desc' => 'Alarma Zona No Definida', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'TA', 'desc' => 'Sabotaje (Tamper)', 'prio' => 4, 'color' => '#ff8c00', 'sound' => 'tamper.mp3'],
            ['code' => 'BV', 'desc' => 'Confirmación de Robo (Verificado)', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'burglar.mp3'],
            ['code' => 'BG', 'desc' => 'Rotura de Cristal', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'BM', 'desc' => 'Robo Perimetral', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'BI', 'desc' => 'Robo Interior', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            
            // --- EVENTOS DE APERTURA / CIERRE (Prioridad 1) ---
            ['code' => 'OP', 'desc' => 'Apertura (Desarmado)', 'prio' => 1, 'color' => '#00bfff', 'sound' => null],
            ['code' => 'CL', 'desc' => 'Cierre (Armado)', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'OR', 'desc' => 'Desarmado tras Alarma', 'prio' => 3, 'color' => '#ffa500', 'sound' => 'warning.mp3'],
            ['code' => 'CR', 'desc' => 'Armado Reciente', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'CG', 'desc' => 'Cierre Área Segura', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'OG', 'desc' => 'Apertura Área Segura', 'prio' => 1, 'color' => '#00bfff', 'sound' => null],
            
            // --- FALLOS TÉCNICOS DE SISTEMA (Prioridad 2 y 3) ---
            ['code' => 'AT', 'desc' => 'Fallo de Corriente AC', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'AR', 'desc' => 'Restauración AC', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'YT', 'desc' => 'Batería Baja Sistema', 'prio' => 3, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'YR', 'desc' => 'Restauración Batería Sistema', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'XT', 'desc' => 'Batería Baja Transmisor', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'XR', 'desc' => 'Restauración Batería Transmisor', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'ET', 'desc' => 'Fallo de Expansor', 'prio' => 3, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'ER', 'desc' => 'Restauración Expansor', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'LT', 'desc' => 'Fallo Línea Telefónica/Red', 'prio' => 3, 'color' => '#ff8c00', 'sound' => 'warning.mp3'],
            ['code' => 'LR', 'desc' => 'Restauración Línea/Red', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            
            // --- TESTS Y MANTENIMIENTO (Prioridad 0 y 1) ---
            ['code' => 'RP', 'desc' => 'Test Automático', 'prio' => 0, 'color' => '#d3d3d3', 'sound' => null],
            ['code' => 'TX', 'desc' => 'Test Manual', 'prio' => 1, 'color' => '#d3d3d3', 'sound' => null],
            ['code' => 'TS', 'desc' => 'Inicio Modo Prueba', 'prio' => 1, 'color' => '#a9a9a9', 'sound' => null],
            ['code' => 'TE', 'desc' => 'Fin Modo Prueba', 'prio' => 1, 'color' => '#a9a9a9', 'sound' => null],
            ['code' => 'LB', 'desc' => 'Inicio Programación Local', 'prio' => 2, 'color' => '#ffd700', 'sound' => null],
            ['code' => 'LX', 'desc' => 'Fin Programación Local', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'RB', 'desc' => 'Inicio Programación Remota', 'prio' => 2, 'color' => '#ffd700', 'sound' => null],
            ['code' => 'RX', 'desc' => 'Fin Programación Remota', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],

            // --- RESTAURACIONES DE ALARMA (Prioridad 1 - Informativo verde) ---
            ['code' => 'FR', 'desc' => 'Restauración Fuego', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'MR', 'desc' => 'Restauración Médica', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'PR', 'desc' => 'Restauración Pánico', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'BR', 'desc' => 'Restauración Robo', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'TR', 'desc' => 'Restauración Tamper', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'UR', 'desc' => 'Restauración Zona', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            
            // --- OTROS EVENTOS COMUNES ---
            ['code' => 'GA', 'desc' => 'Alarma Gas', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'GR', 'desc' => 'Restauración Gas', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'ZA', 'desc' => 'Alarma Congelación', 'prio' => 3, 'color' => '#00bfff', 'sound' => 'warning.mp3'],
            ['code' => 'ZR', 'desc' => 'Restauración Congelación', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'WA', 'desc' => 'Alarma Inundación', 'prio' => 4, 'color' => '#0000ff', 'sound' => 'warning.mp3'],
            ['code' => 'WR', 'desc' => 'Restauración Inundación', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'JA', 'desc' => 'Código Usuario Detectado', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'JL', 'desc' => 'Bloqueo Teclado (Intentos Fallidos)', 'prio' => 3, 'color' => '#ff8c00', 'sound' => 'tamper.mp3'],
        ];

        foreach ($codes as $c) {
            SiaCode::updateOrCreate(['code' => $c['code']], [
                'description' => $c['desc'],
                'priority' => $c['prio'],
                'color_hex' => $c['color'],
                'sound_alert' => $c['sound']
            ]);
        }
        $this->command->info('✅ Diccionario SIA Completo (60+ códigos) cargado.');

        // 3. CLIENTE: PANADERÍA (Empresa Real)
        // Demostración de estructura empresarial
        $customer = Customer::updateOrCreate(
            ['national_id' => 'J-123456789'],
            [
                'type' => 'company', 
                'business_name' => 'Inversiones La Esperanza C.A.', 
                'first_name' => 'Pedro', // Representante
                'last_name' => 'Pérez',  // Representante
                'email' => 'admin@laesperanza.com',
                'phone_1' => '0251-5555555',
                'phone_2' => '0414-5555555',
                'address_billing' => 'Av. 20 con Calle 30, Torre Empresarial, Piso 1, Oficina 4-A',
                'city' => 'Barquisimeto',
                'monitoring_password' => 'Chocolate',
            ]
        );

        // 4. CUENTA DE ALARMA: SUCURSAL CENTRO
        // Geolocalizada para el mapa de Operaciones
        $account = AlarmAccount::updateOrCreate(
            ['account_number' => 'Q28252694'], 
            [
                'customer_id' => $customer->id,
                'service_status' => 'active',
                'branch_name' => 'Sucursal Centro', 
                'installation_address' => 'Calle 30 entre 19 y 20, Local 4, Al lado del Banco',
                'latitude' => 10.065000,
                'longitude' => -69.335000,
                'notes' => 'Panel DSC PowerSeries Neo. Comunicador IP principal, GPRS respaldo.',
                'test_mode_until' => null,
            ]
        );

        // 5. ZONAS DE PRUEBA
        AlarmZone::firstOrCreate(['alarm_account_id' => $account->id, 'zone_number' => '001'], ['name' => 'Magnético Entrada Ppal', 'type' => 'Retardada']);
        AlarmZone::firstOrCreate(['alarm_account_id' => $account->id, 'zone_number' => '002'], ['name' => 'PIR Área Caja', 'type' => 'Instantánea']);
        AlarmZone::firstOrCreate(['alarm_account_id' => $account->id, 'zone_number' => '003'], ['name' => 'PIR Almacén', 'type' => 'Seguimiento']);
        AlarmZone::firstOrCreate(['alarm_account_id' => $account->id, 'zone_number' => '004'], ['name' => 'Pánico Botón Gerencia', 'type' => '24 Horas']);
        AlarmZone::firstOrCreate(['alarm_account_id' => $account->id, 'zone_number' => '005'], ['name' => 'Sensor Humo Cocina', 'type' => 'Fuego']);

        $this->command->info('✅ Base de datos inicializada correctamente.');
    }
}<?php

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
    