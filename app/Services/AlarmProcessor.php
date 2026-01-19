<?php

namespace App\Services;

use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\SiaCode;
use App\Models\Incident; // Si decides crear tickets automáticamente
use Illuminate\Support\Facades\Log;

class AlarmProcessor
{
    public function process($accountNumber, $eventCode, $zoneOrUser, $rawData, $remoteIp)
    {
        // 1. Identificar Cuenta
        $account = AlarmAccount::where('account_number', $accountNumber)->first();
        
        if (!$account) {
            Log::warning("⚠️ Señal de cuenta desconocida: $accountNumber");
            return; // O guardar en una tabla de "Eventos Huérfanos"
        }

        // 2. Identificar Qué pasó (Diccionario SIA)
        $siaConfig = SiaCode::where('code', $eventCode)->first();
        $priority = $siaConfig ? $siaConfig->priority : 1; // Default a 1 si no existe
        $isAutoProcess = ($priority == 0); // 0 = Solo Log (Ej: Test)

        // 3. Lógica Específica por TIPO de evento
        
        // A. TEST AUTOMÁTICO (Keep-Alive)
        if ($eventCode === 'RP' || $eventCode === 'TX') { 
            $account->update([
                'last_checkin_at' => now(),
                'service_status' => 'active' // Reactivar si estaba offline
            ]);
            $isAutoProcess = true; // No molestar al operador
        }

        // B. APERTURA / CIERRE (Armado/Desarmado)
        if ($eventCode === 'OP') { // Open (Desarmado)
            $account->update(['is_armed' => false]);
            // AQUÍ: Verificar si desarmó fuera de horario permitido (Logica de Horarios)
        }
        if ($eventCode === 'CL') { // Close (Armado)
            $account->update(['is_armed' => true]);
        }

        // 4. Guardar el Evento
        $event = AlarmEvent::create([
            'account_number' => $accountNumber,
            'event_code'     => $eventCode,
            'event_type'     => $siaConfig ? $siaConfig->category : 'unknown',
            'zone'           => $zoneOrUser, // Puede ser Zona (BA) o Usuario (OP)
            'ip_address'     => $remoteIp,
            'raw_data'       => $rawData,
            'received_at'    => now(),
            'processed'      => $isAutoProcess, // Si es true, no sale en consola pendiente
            'processed_at'   => $isAutoProcess ? now() : null,
        ]);

        // 5. Si es ALARMA REAL (Prioridad Alta) -> Crear Incidente Operativo
        if ($priority >= 2) {
            // Aquí se podría crear un Ticket en la tabla 'incidents' automáticamente
            // O disparar una notificación WebSocket al Dashboard
            Log::alert("🚨 ALARMA CRÍTICA: Cuenta $accountNumber - $eventCode");
        }

        return $event;
    }
}