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
    /**
     * Procesa una trama SIA cruda y ejecuta la lógica de negocio.
     */
    public function process($accountNumber, $eventCode, $zoneOrUser, $rawData, $remoteIp)
    {
        // 1. Buscar la Cuenta
        $account = AlarmAccount::where('account_number', $accountNumber)->first();
        
        // 2. Buscar Configuración del Código SIA (Prioridad y Significado)
        $siaConfig = SiaCode::where('code', $eventCode)->first();
        
        // Default: Si no existe el código, asumimos que es una Alerta (Prioridad 2)
        $priority = $siaConfig ? $siaConfig->priority : 2; 
        $category = $siaConfig ? $siaConfig->category : 'unknown';

        // Lógica de Autoprocesamiento (Prioridad 0 = Solo Log, 1 = Info sin sonido fuerte)
        $isAutoProcess = ($priority <= 0); 

        // 3. ACTUALIZACIÓN DE ESTADO DE LA CUENTA (Lógica de Negocio)
        if ($account) {
            $updateData = ['last_signal_at' => now()];

            // A. Test Automático (Heartbeat)
            if ($eventCode === 'RP' || $eventCode === 'TX') {
                $updateData['last_checkin_at'] = now();
                $updateData['service_status'] = 'active'; // Reactivar si estaba offline
                $isAutoProcess = true; // Forzar autoproceso
            }

            // B. Armado / Desarmado
            if ($eventCode === 'OP') { // Apertura (Open)
                $updateData['is_armed'] = false;
                // TODO: Aquí validaríamos si el horario permite abrir ahora
            }
            if ($eventCode === 'CL') { // Cierre (Close)
                $updateData['is_armed'] = true;
            }

            $account->update($updateData);
        }

        // 4. Enriquecer Información (Buscar Nombre de Zona o Usuario)
        $descriptionSuffix = "";
        
        if ($account) {
            // Si es alarma (BA, FA), buscamos la ZONA
            if (in_array($category, ['alarm', 'fire', 'panic'])) {
                $zone = AlarmZone::where('alarm_account_id', $account->id)
                                 ->where('zone_number', $zoneOrUser) // Ej: "001"
                                 ->first();
                if ($zone) {
                    $descriptionSuffix = " - Zona: " . $zone->name . " (" . $zone->type . ")";
                }
            }
            
            // Si es control de acceso (OP, CL), buscamos el USUARIO
            if (in_array($category, ['status', 'access'])) {
                $user = PanelUser::where('alarm_account_id', $account->id)
                                 ->where('user_number', $zoneOrUser) // Ej: "005"
                                 ->first();
                if ($user) {
                    $descriptionSuffix = " - Usuario: " . $user->name . " (" . $user->role . ")";
                }
            }
        }

        // 5. Guardar el Evento en Historial
        $event = AlarmEvent::create([
            'account_number' => $accountNumber,
            'event_code'     => $eventCode,
            'event_type'     => $category,
            'zone'           => $zoneOrUser,
            'ip_address'     => $remoteIp,
            'raw_data'       => $rawData . $descriptionSuffix, // Guardamos la info enriquecida en raw o un campo nuevo 'details'
            'received_at'    => now(),
            'processed'      => $isAutoProcess,
            'processed_at'   => $isAutoProcess ? now() : null,
        ]);

        return $event;
    }
}