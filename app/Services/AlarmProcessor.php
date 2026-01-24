<?php

namespace App\Services;

use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\SiaCode;
use App\Models\AlarmZone;
use Illuminate\Support\Facades\Log;

class AlarmProcessor
{
    public function process($accountNumber, $eventCode, $zoneOrUser, $rawData, $remoteIp)
    {
        Log::info("SIA Receiver: Procesando evento $eventCode para cuenta $accountNumber");

        // 1. Buscar la Cuenta en la DB
        $account = AlarmAccount::where('account_number', $accountNumber)->first();
        
        // Si la cuenta no existe en tu sistema, no podemos guardar el evento (error de FK)
        if (!$account) {
            Log::warning("SIA: Cuenta desconocida $accountNumber. Evento ignorado.");
            return null;
        }

        // 2. Buscar descripción del código SIA
        $siaConfig = SiaCode::where('code', $eventCode)->first();
        $description = $siaConfig ? $siaConfig->description : 'Evento Desconocido';
        $priority = $siaConfig ? $siaConfig->priority : 3;

        // 3. Obtener nombre de la zona (si aplica)
        $zoneName = null;
        if ($zoneOrUser) {
            $zone = AlarmZone::where('alarm_account_id', $account->id)
                             ->where('zone_number', $zoneOrUser)
                             ->first();
            if ($zone) {
                $zoneName = " - " . $zone->name;
            }
        }

        // 4. Actualizar la última conexión de la cuenta
        $account->update(['updated_at' => now()]); // O usa una columna last_connection si la tienes

        // 5. GUARDAR EL EVENTO (Aquí estaba el fallo)
        try {
            $event = AlarmEvent::create([
                'alarm_account_id' => $account->id, // <--- LA CLAVE: Usamos el ID interno
                'event_code'       => $eventCode,
                'code'             => $eventCode,   // Guardamos en ambos por compatibilidad
                'description'      => $description . ($zoneName ?? ''),
                'zone'             => $zoneOrUser,
                'partition'        => '01',         // Valor por defecto si no viene en trama
                'raw_data'         => $rawData,
                'received_at'      => now(),
                'processed'        => ($priority <= 1) // Auto-procesar eventos de baja prioridad
            ]);

            Log::info("SIA: Evento guardado correctamente ID: " . $event->id);
            return $event;

        } catch (\Exception $e) {
            Log::error("SIA Database Error: " . $e->getMessage());
            return null;
        }
    }
}