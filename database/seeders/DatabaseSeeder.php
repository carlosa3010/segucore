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
        // 1. CREAR SUPER ADMIN
        if (!User::where('email', 'admin@segusmart.com')->exists()) {
            User::create([
                'name' => 'Carlos Morales',
                'email' => 'admin@segusmart.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin', // Asegúrate de tener esta columna o quitar esta línea si usas Spatie
            ]);
            $this->command->info('✅ Usuario Admin creado.');
        }

        // 2. DICCIONARIO SIA COMPLETO
        $codes = [
            ['code' => 'MA', 'desc' => 'Emergencia Médica', 'prio' => 5, 'color' => '#0000ff', 'sound' => 'panic.mp3'],
            ['code' => 'BA', 'desc' => 'Alarma de Robo', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'FA', 'desc' => 'Alarma de Fuego', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'KA', 'desc' => 'Alarma de Calor/Fuego', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'HA', 'desc' => 'Coacción / Atraco', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'panic.mp3'],
            ['code' => 'AA', 'desc' => 'Pánico Audible', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'panic.mp3'],
            ['code' => 'AB', 'desc' => 'Alarma 24H', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'PA', 'desc' => 'Pánico Silencioso', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'panic.mp3'],
            ['code' => 'AD', 'desc' => 'Robo Interior', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'TA', 'desc' => 'Sabotaje (Tamper)', 'prio' => 4, 'color' => '#ff8c00', 'sound' => 'tamper.mp3'],
            ['code' => 'BV', 'desc' => 'Alarma Confirmada (Video)', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'AT', 'desc' => 'Fallo de Corriente AC', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'YT', 'desc' => 'Batería Sistema Baja', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'YP', 'desc' => 'Protección Sobrevoltaje', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'XT', 'desc' => 'Batería Repetidor Baja', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'AL', 'desc' => 'Voltaje Bajo Expansor', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'YC', 'desc' => 'Fallo al Subir Reporte', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'OP', 'desc' => 'Apertura (Desarmado)', 'prio' => 1, 'color' => '#00bfff', 'sound' => null],
            ['code' => 'CL', 'desc' => 'Cierre (Armado)', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'CD', 'desc' => 'Fallo Auto-Armado', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'AR', 'desc' => 'Restauración AC / Fallo Salida', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'AS', 'desc' => 'Fallo Apagar Salida', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'JA', 'desc' => 'Password Incorrecto', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'RP', 'desc' => 'Test Periódico', 'prio' => 0, 'color' => '#d3d3d3', 'sound' => null],
            ['code' => 'TS', 'desc' => 'Modo Prueba Iniciado', 'prio' => 0, 'color' => '#d3d3d3', 'sound' => null],
            ['code' => 'LB', 'desc' => 'Entrando a Programación', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'LX', 'desc' => 'Saliendo de Programación', 'prio' => 1, 'color' => '#808080', 'sound' => null],
            ['code' => 'ZS', 'desc' => 'Pre-Alarma Temp Baja', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'ZA', 'desc' => 'Alarma Temperatura Baja', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'BB', 'desc' => 'Alarma Robo Teclado', 'prio' => 4, 'color' => '#ff4500', 'sound' => 'burglar.mp3'],
            ['code' => 'CI', 'desc' => 'Fallo al Armar', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'BK', 'desc' => 'Pérdida Video', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'BP', 'desc' => 'Fallo Subir Foto', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'BQ', 'desc' => 'Fallo Enviar Email', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'BR', 'desc' => 'Cámara Desconectada', 'prio' => 2, 'color' => '#ffd700', 'sound' => null],
            ['code' => 'BU', 'desc' => 'Consulta Alarma Fuego', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'BW', 'desc' => 'Consulta Médica', 'prio' => 5, 'color' => '#0000ff', 'sound' => 'panic.mp3'],
            ['code' => 'RS', 'desc' => 'Fallo Update Firmware', 'prio' => 2, 'color' => '#ffd700', 'sound' => 'warning.mp3'],
            ['code' => 'MH', 'desc' => 'Restauración Médica', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'FH', 'desc' => 'Restauración Fuego', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'KH', 'desc' => 'Restauración Fuego', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'HH', 'desc' => 'Restauración Pánico/Coacción', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'CH', 'desc' => 'Restauración Pánico Audible', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'PH', 'desc' => 'Restauración Pánico', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'CK', 'desc' => 'Restauración Robo Interior', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'TR', 'desc' => 'Restauración Tamper', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'YR', 'desc' => 'Restauración Batería', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'XR', 'desc' => 'Restauración Bat. Repetidor', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'LR', 'desc' => 'Restauración Canal Principal', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'YK', 'desc' => 'Restauración Subida Reporte', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'TE', 'desc' => 'Fin Modo Prueba', 'prio' => 0, 'color' => '#d3d3d3', 'sound' => null],
            ['code' => 'NR', 'desc' => 'Red Celular Conectada', 'prio' => 1, 'color' => '#32cd32', 'sound' => null],
            ['code' => 'DQ', 'desc' => 'Fin Consulta Fuego', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'fire_alarm.mp3'],
            ['code' => 'DU', 'desc' => 'Fin Consulta Coacción', 'prio' => 5, 'color' => '#ff0000', 'sound' => 'panic.mp3'],
            ['code' => 'DV', 'desc' => 'Fin Consulta Médica', 'prio' => 5, 'color' => '#0000ff', 'sound' => 'panic.mp3'],
        ];

        foreach ($codes as $c) {
            SiaCode::updateOrCreate(['code' => $c['code']], [
                'description' => $c['desc'],
                'priority' => $c['prio'],
                'color_hex' => $c['color'],
                'sound_alert' => $c['sound']
            ]);
        }
        $this->command->info('✅ Diccionario SIA de IP Receiver cargado.');

        // 3. CLIENTE DE PRUEBA (ACTUALIZADO AL NUEVO ESQUEMA)
        $customer = Customer::updateOrCreate(
            ['national_id' => 'J-123456789'],
            [
                'first_name' => 'Panadería',
                'last_name' => 'La Esperanza', // REQUERIDO: Campo nuevo
                'email' => 'cliente@prueba.com',
                'phone_1' => '0414-5555555', // CORREGIDO: De phone_primary a phone_1
                'address' => 'Av. 20 con Calle 30, Barquisimeto', // CORREGIDO: De address_monitoring a address
                'city' => 'Barquisimeto', // REQUERIDO: Campo nuevo
                'monitoring_password' => 'Chocolate',
            ]
        );

        $account = AlarmAccount::updateOrCreate(
            ['account_number' => 'Q28252694'], 
            [
                'customer_id' => $customer->id,
                'service_status' => 'active',
            ]
        );

        // Zonas de prueba
        AlarmZone::firstOrCreate(['alarm_account_id' => $account->id, 'zone_number' => '001'], ['name' => 'Puerta Principal', 'type' => 'Retardada']);
        AlarmZone::firstOrCreate(['alarm_account_id' => $account->id, 'zone_number' => '002'], ['name' => 'Caja', 'type' => 'Instantánea']);
        
        $this->command->info('✅ Cliente y Cuenta Q28252694 listos.');
    }
}