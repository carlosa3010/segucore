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
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

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

        // 3. SIA CODES
        $codes = [
    // --- ALARMAS DE ALTA PRIORIDAD (ROJO/NARANJA) ---
    ['code' => 'MA', 'desc' => 'Emergencia Médica', 'prio' => 5, 'color' => '#dc2626'], // Rojo
    ['code' => 'BA', 'desc' => 'Alarma de Robo', 'prio' => 4, 'color' => '#ea580c'], // Naranja
    ['code' => 'FA', 'desc' => 'Alarma de Incendio', 'prio' => 5, 'color' => '#dc2626'],
    ['code' => 'KA', 'desc' => 'Alarma de Calor (Incendio)', 'prio' => 5, 'color' => '#dc2626'],
    ['code' => 'HA', 'desc' => 'Coacción (Atraco)', 'prio' => 5, 'color' => '#dc2626'],
    ['code' => 'AA', 'desc' => 'Pánico Audible', 'prio' => 5, 'color' => '#dc2626'],
    ['code' => 'AB', 'desc' => 'Alarma 24 Horas', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'PA', 'desc' => 'Pánico Silencioso', 'prio' => 5, 'color' => '#dc2626'],
    ['code' => 'AD', 'desc' => 'Robo Interior', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'TA', 'desc' => 'Sabotaje de Dispositivo (Tamper)', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'BV', 'desc' => 'Alarma Confirmada', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'AE', 'desc' => 'Circuito Abierto en BUS', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'AF', 'desc' => 'Cortocircuito en BUS', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'AG', 'desc' => 'Detección de Movimiento', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'GA', 'desc' => 'Alarma de Gas', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'WA', 'desc' => 'Alarma de Inundación/Agua', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'AO', 'desc' => 'Zona Abierta (Alarma)', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'AP', 'desc' => 'Zona en Corto (Alarma)', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'IA', 'desc' => 'Alarma Sísmica/Taladro', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'AX', 'desc' => 'Alarma PIR', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'KS', 'desc' => 'Pre-Alarma Alta Temperatura', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'ZS', 'desc' => 'Pre-Alarma Baja Temperatura', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'ZA', 'desc' => 'Alarma Baja Temperatura', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'KT', 'desc' => 'Alarma de Temperatura', 'prio' => 4, 'color' => '#ea580c'],
    ['code' => 'BB', 'desc' => 'Robo Teclado/Llavero', 'prio' => 4, 'color' => '#ea580c'],

    // --- FALLOS TÉCNICOS (AMARILLO) ---
    ['code' => 'AT', 'desc' => 'Fallo de Alimentación AC', 'prio' => 3, 'color' => '#eab308'], // Amarillo
    ['code' => 'YT', 'desc' => 'Batería Baja de Sistema', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'ZY', 'desc' => 'Reinicio del Panel', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'YM', 'desc' => 'Batería Desconectada', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'YI', 'desc' => 'Protección Sobrecorriente Activada', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'YP', 'desc' => 'Protección Sobrevoltaje Activada', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'AI', 'desc' => 'Corto en Salida de Energía', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'ET', 'desc' => 'Fallo de Expansor', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'AJ', 'desc' => 'Impresora Desconectada', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'XT', 'desc' => 'Batería Baja en Repetidor', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'AL', 'desc' => 'Bajo Voltaje en Expansor', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'XL', 'desc' => 'Sirena Inalámbrica Desconectada', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'LT', 'desc' => 'Fallo Canal Principal ATP', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'AM', 'desc' => 'Línea Telefónica Cortada', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'YC', 'desc' => 'Fallo al subir reporte', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'FT', 'desc' => 'Fallo Sensor Detector', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'AN', 'desc' => 'Fallo Supervisión BUS', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'NT', 'desc' => 'Red Celular Desconectada', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'XQ', 'desc' => 'Excepción Señal RF', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BJ', 'desc' => 'Dispositivo Bloqueado', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BK', 'desc' => 'Pérdida Señal de Video', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BL', 'desc' => 'Formato E/S No Coincide', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BM', 'desc' => 'Excepción Entrada Video', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BN', 'desc' => 'Disco Duro Lleno', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BO', 'desc' => 'Error en Disco Duro', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BP', 'desc' => 'Fallo Subida Imagen', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BQ', 'desc' => 'Fallo Envío Email', 'prio' => 3, 'color' => '#eab308'],
    ['code' => 'BR', 'desc' => 'Cámara IP Desconectada', 'prio' => 3, 'color' => '#eab308'],

    // --- ESTADOS Y CONTROL DE ACCESO (AZUL/NEUTRO) ---
    ['code' => 'OP', 'desc' => 'Desarmado (Apertura)', 'prio' => 2, 'color' => '#3b82f6'], // Azul
    ['code' => 'OA', 'desc' => 'Desarmado Automático', 'prio' => 2, 'color' => '#3b82f6'],
    ['code' => 'CS', 'desc' => 'Desarmado por Llave', 'prio' => 2, 'color' => '#3b82f6'],
    ['code' => 'CL', 'desc' => 'Armado (Cierre)', 'prio' => 2, 'color' => '#3b82f6'], // Nota: CL a veces es Cierre o BUS Restore, depende contexto. Asumo Cierre por OP/CL standard
    ['code' => 'NL', 'desc' => 'Armado Parcial (Stay)', 'prio' => 2, 'color' => '#3b82f6'],
    ['code' => 'CW', 'desc' => 'Armado Instantáneo', 'prio' => 2, 'color' => '#3b82f6'],
    ['code' => 'CX', 'desc' => 'Armado Forzado', 'prio' => 2, 'color' => '#3b82f6'],
    ['code' => 'OS', 'desc' => 'Armado por Llave', 'prio' => 2, 'color' => '#3b82f6'],
    ['code' => 'LB', 'desc' => 'Entrando a Programación', 'prio' => 3, 'color' => '#64748b'],
    ['code' => 'LX', 'desc' => 'Saliendo de Programación', 'prio' => 3, 'color' => '#64748b'],
    ['code' => 'TS', 'desc' => 'Modo Prueba Iniciado', 'prio' => 1, 'color' => '#64748b'],
    ['code' => 'TE', 'desc' => 'Modo Prueba Finalizado', 'prio' => 1, 'color' => '#64748b'],
    ['code' => 'RP', 'desc' => 'Test Periódico (Keep Alive)', 'prio' => 1, 'color' => '#22c55e'], // Verde

    // --- RESTAURACIONES (VERDE - IMPORTANTE PARA TU SOLICITUD) ---
    ['code' => 'MH', 'desc' => 'Restauración Médica', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'FH', 'desc' => 'Restauración Incendio', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'KH', 'desc' => 'Restauración Fuego/Calor', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'HH', 'desc' => 'Restauración Coacción', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'CH', 'desc' => 'Restauración Pánico Audible', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'PH', 'desc' => 'Restauración Pánico', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'CK', 'desc' => 'Restauración Robo Interior', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'TR', 'desc' => 'Restauración Sabotaje', 'prio' => 1, 'color' => '#22c55e'],
    
    // RESTAURACIONES TÉCNICAS (RED, BATERIA, ETC)
    ['code' => 'YR', 'desc' => 'Batería de Sistema Restaurada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'XR', 'desc' => 'Batería Repetidor Restaurada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'YJ', 'desc' => 'Protección Sobrecorriente Restaurada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'YQ', 'desc' => 'Protección Sobrevoltaje Restaurada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'CQ', 'desc' => 'Corto Salida Energía Restaurado', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'ER', 'desc' => 'Fallo Expansor Restaurado', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'LR', 'desc' => 'Canal Principal ATP Restaurado', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'YK', 'desc' => 'Subida Reportes Restaurada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'FJ', 'desc' => 'Fallo Sensor Restaurado', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'CV', 'desc' => 'Supervisión BUS Restaurada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'NR', 'desc' => 'Red Celular Conectada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'CU', 'desc' => 'Línea Telefónica Restaurada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'DS', 'desc' => 'Cámara IP Conectada', 'prio' => 1, 'color' => '#22c55e'],
    ['code' => 'QU', 'desc' => 'Anulación Zona Restaurada', 'prio' => 1, 'color' => '#22c55e'],
];
        foreach ($codes as $c) {
            SiaCode::updateOrCreate(['code' => $c['code']], [
                'description' => $c['desc'],
                'priority' => $c['prio'],
                'color_hex' => $c['color']
            ]);
        }

        // 4. CLIENTE (Usando national_id para consistencia)
        $customer = Customer::updateOrCreate(
            ['national_id' => 'J-123456789'], 
            [
                'type' => 'company', 
                'business_name' => 'Inversiones La Esperanza C.A.', 
                'first_name' => 'Pedro', 
                'last_name' => 'Pérez',
                'email' => 'admin@laesperanza.com',
                'phone_1' => '0414-5555555',
                'address' => 'Av. 20 con Calle 30, Barquisimeto',
                'address_billing' => 'Av. 20 con Calle 30, Barquisimeto', // Requerido
                'city' => 'Barquisimeto',
                'is_active' => true,
                'status' => 'active'
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

        // Zonas y Particiones
        AlarmZone::firstOrCreate(
            ['alarm_account_id' => $account->id, 'zone_number' => '001'],
            ['name' => 'Entrada Principal', 'type' => 'Retardada']
        );

        DB::table('alarm_partitions')->insertOrIgnore([
            'alarm_account_id' => $account->id,
            'partition_number' => 1,
            'name' => 'General',
            'status' => 'armed',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 6. CONDUCTOR Y GPS
        $driver = Driver::create([
            'customer_id' => $customer->id,
            'first_name' => 'Juan',
            'last_name' => 'Conductor',
            'full_name' => 'Juan Conductor',
            'phone' => '0412-0000000',
            'status' => 'active',
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