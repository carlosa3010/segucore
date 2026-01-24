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
        Log::info("SIA: Procesando cuenta: $accountNumber | Evento: $eventCode");

        // 1. Buscar el ID real de la cuenta
        $account = AlarmAccount::where('account_number', $accountNumber)->first();

        if (!$account) {
            Log::warning("SIA: Cuenta $accountNumber no encontrada. Evento descartado.");
            return null;
        }

        // 2. Buscar descripción del código SIA
        $siaConfig = SiaCode::where('code', $eventCode)->first();
        $description = $siaConfig ? $siaConfig->description : 'Evento Desconocido';
        $priority = $siaConfig ? $siaConfig->priority : 3;

        // 3. Buscar nombre de zona (Opcional)
        $zoneName = '';
        if ($zoneOrUser) {
            $zone = AlarmZone::where('alarm_account_id', $account->id)
                             ->where('zone_number', $zoneOrUser)
                             ->first();
            if ($zone) $zoneName = " - " . $zone->name;
        }

        // 4. Actualizar "última señal" de la cuenta
        $account->update(['updated_at' => now(), 'service_status' => 'active']);

        // 5. GUARDAR EL EVENTO (Usando el ID correcto)
        try {
            $event = AlarmEvent::create([
                'alarm_account_id' => $account->id, // <--- ESTO ES LO IMPORTANTE
                'event_code'       => $eventCode,
                'code'             => $eventCode,
                'description'      => $description . $zoneName,
                'zone'             => $zoneOrUser,
                'partition'        => '01',
                'raw_data'         => $rawData,
                'received_at'      => now(),
                'processed'        => ($priority <= 1)
            ]);

            return $event;

        } catch (\Exception $e) {
            Log::error("SIA Error DB: " . $e->getMessage());
            return null;
        }
    }
}