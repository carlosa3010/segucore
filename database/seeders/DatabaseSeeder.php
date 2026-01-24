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
            ['code' => 'MA', 'desc' => 'Medical Alarm', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'BA', 'desc' => 'Burglary Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'FA', 'desc' => 'Fire Alarm', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'KA', 'desc' => 'Fire Alarm', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'HA', 'desc' => 'Duress', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'AA', 'desc' => 'Audible Panic Alarm', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'AB', 'desc' => '24H Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'PA', 'desc' => 'Panic Alarm', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'AD', 'desc' => 'Interior Burglary Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'TA', 'desc' => 'Device Tampered', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'BV', 'desc' => 'Confirmed Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AE', 'desc' => 'BUS Open-circuit Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AF', 'desc' => 'BUS Short-circuit Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AG', 'desc' => 'Device Motion Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'GA', 'desc' => 'Gas Leakage Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'WA', 'desc' => 'Water Leakage Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AH', 'desc' => 'Zone Early-Warning', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AT', 'desc' => 'AC Power Loss', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'YT', 'desc' => 'Low System Battery', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'ZY', 'desc' => 'Control Panel Reboot', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'YM', 'desc' => 'Battery Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'YI', 'desc' => 'Overcurrent Protection Triggered', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'YP', 'desc' => 'Overvoltage Protection Triggered', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AI', 'desc' => 'Power Output Short Circuit', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'ET', 'desc' => 'Expander Fault', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AJ', 'desc' => 'Printer Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'XT', 'desc' => 'Repeater Battery Low', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AL', 'desc' => 'Expander Low Voltage', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'XL', 'desc' => 'Wireless Siren Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'LT', 'desc' => 'Main Channel ATP Fault', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AM', 'desc' => 'Telephone Line Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'YC', 'desc' => 'Uploading Report Failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'FT', 'desc' => 'Detector Sensor Fault', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AN', 'desc' => 'BUS Supervision Fault', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AO', 'desc' => 'Zone Open-circuit Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AP', 'desc' => 'Zone Short-circuit Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'OP', 'desc' => 'Disarming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'OA', 'desc' => 'Auto Disarming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BC', 'desc' => 'Alarm Clearing', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'CS', 'desc' => 'Keyswitch Zone Disarming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AQ', 'desc' => 'Turn On Output by Schedule', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CT', 'desc' => 'Late to Disarm', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CD', 'desc' => 'Auto Arming Failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AR', 'desc' => 'Turning On Output Failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AS', 'desc' => 'Turning Off Output Failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'JA', 'desc' => 'Incorrect Password', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'QB', 'desc' => 'Zone Bypassed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AU', 'desc' => 'Group Bypass', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'AV', 'desc' => 'Manual Report Test', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'RP', 'desc' => 'Periodic Report Test', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'TS', 'desc' => 'Test Mode Entered', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'AW', 'desc' => 'Telephone Connection Test', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'LB', 'desc' => 'Enter Programming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'LX', 'desc' => 'Exit Programming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'IA', 'desc' => 'Drilling alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AX', 'desc' => 'PIR Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AY', 'desc' => 'Sudden Increase of Sound Intensity Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'AZ', 'desc' => 'Sudden Decrease of Sound Intensity Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'KS', 'desc' => 'High Temperature Pre-Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'ZS', 'desc' => 'Low Temperature Pre-Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'ZA', 'desc' => 'Low Temperature Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'EA', 'desc' => 'Region Exiting Detection', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'KT', 'desc' => 'Temperature Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'BB', 'desc' => 'Keypad/Keyfob Burglary Alarm', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'CI', 'desc' => 'Arming Failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DK', 'desc' => 'Keypad Locked', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BD', 'desc' => 'Unregistered Tag', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BE', 'desc' => 'Keypad Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BF', 'desc' => 'KBUS Relay Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BG', 'desc' => 'KBUS GP/K Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BH', 'desc' => 'KBUS MN/K Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BI', 'desc' => 'Radar Transmitter Fault', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'NT', 'desc' => 'Cellular Data Network Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'XQ', 'desc' => 'RF Signal Exception', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BJ', 'desc' => 'Device Blocked', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BK', 'desc' => 'Video Signal Loss', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BL', 'desc' => 'Input/Output Format Unmatched', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BM', 'desc' => 'Video Input Exception', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BN', 'desc' => 'Full HDD', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BO', 'desc' => 'HDD Exception', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BP', 'desc' => 'Upload Picture Failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BQ', 'desc' => 'Sending Email Failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BR', 'desc' => 'Network Camera Disconnected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BS', 'desc' => 'Duty Checking', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BT', 'desc' => 'Post Response', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BU', 'desc' => 'Fire Alarm Consulting', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'BW', 'desc' => 'Emergency Medical Alarm Consulting', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'BX', 'desc' => 'BUS Query', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BY', 'desc' => 'BUS Registration', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'BZ', 'desc' => 'Single-Zone Disarming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CA', 'desc' => 'Single-Zone Alarm Cleared', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'CB', 'desc' => 'Detector Deleted', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CC', 'desc' => 'Business Consulting', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CE', 'desc' => 'Wireless Repeater Deleted', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CF', 'desc' => 'Wireless Siren Deleted', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CG', 'desc' => 'Wireless Device Deleted', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'XI', 'desc' => 'Panel was reset to factory settings', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'RB', 'desc' => 'Updating firmware', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'RS', 'desc' => 'The firmware update failed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'UR', 'desc' => 'User has been removed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'TB', 'desc' => 'Notifications about the state of the lid are disabled', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'MH', 'desc' => 'Medical Alarm Restored', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'FH', 'desc' => 'Fire Alarm Restored', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'KH', 'desc' => 'Fire Alarm Restored', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'HH', 'desc' => 'Silent Panic Alarm Restored', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'CH', 'desc' => 'Audible Panic Alarm Restored', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'PH', 'desc' => 'Panic Alarm Restored', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'CK', 'desc' => 'Interior Burglary Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'TR', 'desc' => 'Device Tamper Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'CL', 'desc' => 'BUS Open-circuit Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'CN', 'desc' => 'BUS Short-circuit Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'CO', 'desc' => 'Device Motion Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'GH', 'desc' => 'Gas Leakage Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'WH', 'desc' => 'Water Leakage Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'CP', 'desc' => 'Zone Early-Warning Dismissed', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'YR', 'desc' => 'Low System Battery Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'YJ', 'desc' => 'Overcurrent Protection Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'YQ', 'desc' => 'Overvoltage Protection Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'CQ', 'desc' => 'Power Output Short Circuit Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'ER', 'desc' => 'Expander Fault Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'CR', 'desc' => 'Printer Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'XR', 'desc' => 'Repeater Battery Voltage Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'XC', 'desc' => 'Wireless Siren Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'LR', 'desc' => 'Main Channel ATP Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'CU', 'desc' => 'Telephone Line Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'YK', 'desc' => 'Report Uploading Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'FJ', 'desc' => 'Detector Sensor Fault Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'CV', 'desc' => 'BUS Supervision Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'CW', 'desc' => 'Instant Arming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'OS', 'desc' => 'Keyswitch Zone Arming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'NL', 'desc' => 'Stay Arming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'CX', 'desc' => 'Forced Arming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'QU', 'desc' => 'Zone Bypass Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'CZ', 'desc' => 'Group Bypass Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'TE', 'desc' => 'Test Mode Exited', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'IR', 'desc' => 'Drilling Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'DE', 'desc' => 'Sudden Increase of Sound Intensity Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'DF', 'desc' => 'Sudden Decrease of Sound Intensity Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'DC', 'desc' => 'Audio Input Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'KR', 'desc' => 'High Temperature Pre-Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'ZR', 'desc' => 'Low Temperature Pre-Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'ZH', 'desc' => 'Low Temperature Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'KJ', 'desc' => 'Temperature Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'DO', 'desc' => 'Keypad Unlocked', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DH', 'desc' => 'Keypad Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DI', 'desc' => 'KBUS Relay Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DG', 'desc' => 'KBUS MN/K Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DL', 'desc' => 'Radar Transmitter Restored', 'prio' => 1, 'color' => '#32cd32'],
            ['code' => 'NR', 'desc' => 'Cellular Data Network Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'XH', 'desc' => 'Normal RF Signal', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DM', 'desc' => 'Device Blocking Alarm Restored', 'prio' => 4, 'color' => '#ff4500'],
            ['code' => 'DN', 'desc' => 'Free HDD', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DS', 'desc' => 'Network Camera Connected', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DQ', 'desc' => 'Fire Alarm Consulting Over', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'DU', 'desc' => 'Duress Alarm Consulting Over', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'DV', 'desc' => 'Emergency Medical Alarm Consulting Over', 'prio' => 5, 'color' => '#ff0000'],
            ['code' => 'DW', 'desc' => 'Patrol', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DX', 'desc' => 'Single-Zone Arming', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DY', 'desc' => 'Detector Added', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'DZ', 'desc' => 'Business Consulting Over', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'EB', 'desc' => 'Wireless Repeater Added', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'EC', 'desc' => 'Wireless Siren Added', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'ED', 'desc' => 'Wireless Device Added', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'UA', 'desc' => 'New user has been added', 'prio' => 3, 'color' => '#ffd700'],
            ['code' => 'TU', 'desc' => 'Notifications about the state of the lid are enabled', 'prio' => 3, 'color' => '#ffd700'],
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