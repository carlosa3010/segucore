<?php

namespace App\Services;

use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\SiaCode;
use App\Models\AlarmZone;
use App\Models\PanelUser;
use Illuminate\Support\Facades\Log;

class AlarmProcessor
{
    public function process($accountNumber, $eventCode, $zoneOrUser, $rawData, $remoteIp)
    {
        // 1. Buscar la Cuenta
        $account = AlarmAccount::where('account_number', $accountNumber)->first();
        
        // SI NO EXISTE LA CUENTA, NO PODEMOS GUARDAR EL EVENTO (Por restricción de llave foránea)
        if (!$account) {
            Log::warning("SIA: Intento de señal de cuenta desconocida: $accountNumber desde $remoteIp");
            return null; // O podrías crear una lógica para eventos huérfanos
        }

        // 2. Buscar Configuración SIA
        $siaConfig = SiaCode::where('code', $eventCode)->first();
        $priority = $siaConfig ? $siaConfig->priority : 2; 
        
        // En tu tabla SiaCode no vi la columna 'category', así que usamos defaults o lo agregas
        // Asumo 'category' basándome en prioridad para este ejemplo
        $category = 'alarm'; 
        if ($priority == 1) $category = 'status';
        if ($priority == 5) $category = 'panic';

        $isAutoProcess = ($priority <= 1); 

        // 3. ACTUALIZACIÓN DE ESTADO
        $updateData = ['last_signal_at' => now()]; // Asegúrate que tu migración tenga esta columna en alarm_accounts

        if ($eventCode === 'RP' || $eventCode === 'TX') {
            $updateData['service_status'] = 'active'; 
            $isAutoProcess = true;
        }
        if ($eventCode === 'OP') {
            $account->monitoring_status = 'disarmed'; // Actualizamos estado monitor
        }
        if ($eventCode === 'CL') {
            $account->monitoring_status = 'armed';
        }
        
        $account->save(); // Guardar cambios en la cuenta

        // 4. Enriquecer Información
        $descriptionSuffix = "";
        
        // Lógica de Zonas (Simplificada para que no falle si no hay datos)
        if ($category == 'alarm' || $category == 'fire') {
            $zone = AlarmZone::where('alarm_account_id', $account->id)
                             ->where('zone_number', $zoneOrUser)
                             ->first();
            if ($zone) {
                $descriptionSuffix = " - Zona: " . $zone->name;
            }
        }

        // 5. GUARDAR EL EVENTO (CORREGIDO)
        try {
            $event = AlarmEvent::create([
                'alarm_account_id' => $account->id, // <--- AQUÍ ESTABA EL ERROR (Usar ID, no string)
                'event_code'     => $eventCode,
                // 'event_type'     => $category, // Comenta esto si no tienes la columna en DB
                'zone'           => $zoneOrUser,
                // 'ip_address'     => $remoteIp, // Comenta esto si no tienes la columna en DB
                'raw_data'       => $rawData . $descriptionSuffix,
                'received_at'    => now(),
                'processed'      => $isAutoProcess,
                // 'processed_at'   => $isAutoProcess ? now() : null, // Comenta si da error
            ]);

            return $event;

        } catch (\Exception $e) {
            Log::error("Error guardando evento: " . $e->getMessage());
            return null;
        }
    }
}