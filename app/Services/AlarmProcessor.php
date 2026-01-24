<?php

namespace App\Services;

use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\SiaCode;
use App\Models\AlarmZone;
use Illuminate\Support\Facades\Log;

class AlarmProcessor
{
    /**
     * Procesa la trama SIA/CID recibida
     */
    public function process($accountNumber, $eventCode, $zoneOrUser, $rawData, $remoteIp = null)
    {
        Log::info("SIA: Recibido $eventCode de cuenta $accountNumber");

        // 1. BUSCAR LA CUENTA (Paso crítico: Convertir String a ID)
        $account = AlarmAccount::where('account_number', $accountNumber)->first();

        // Si la cuenta no existe, es imposible asociar el evento en una DB relacional
        if (!$account) {
            Log::warning("SIA: Cuenta desconocida $accountNumber. Evento descartado.");
            return null;
        }

        // 2. ENRIQUECER DATOS (Códigos SIA y Zonas)
        $siaInfo = SiaCode::where('code', $eventCode)->first();
        $description = $siaInfo ? $siaInfo->description : 'Evento desconocido';
        $priority = $siaInfo ? $siaInfo->priority : 3;

        // Buscar nombre de zona si aplica
        $zoneName = '';
        if ($zoneOrUser) {
            $zone = AlarmZone::where('alarm_account_id', $account->id)
                             ->where('zone_number', $zoneOrUser)
                             ->first();
            if ($zone) $zoneName = " - " . $zone->name;
        }

        // 3. ACTUALIZAR ESTADO DE LA CUENTA (Heartbeat)
        $account->update([
            'updated_at' => now(), // Marca de "última señal"
            'service_status' => 'active' // Reactivar si estaba suspendida
        ]);

        // 4. GUARDAR EL EVENTO (Corrección de columna)
        try {
            $event = AlarmEvent::create([
                'alarm_account_id' => $account->id, // <--- USAR ID, NO STRING
                'event_code'       => $eventCode,
                'code'             => $eventCode,   // Compatibilidad
                'description'      => $description . $zoneName,
                'zone'             => $zoneOrUser,
                'partition'        => '01',         // Valor default
                'raw_data'         => $rawData,
                'received_at'      => now(),
                'processed'        => ($priority <= 1) // Auto-procesar señales de rutina
            ]);

            Log::info("SIA: Evento guardado con éxito. ID: " . $event->id);
            return $event;

        } catch (\Exception $e) {
            Log::error("SIA Error al guardar en DB: " . $e->getMessage());
            return null;
        }
    }
}