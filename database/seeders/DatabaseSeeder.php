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
        AlarmZone::firstOrCreate(['alarm