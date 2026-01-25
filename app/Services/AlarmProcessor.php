<?php

namespace App\Services;

use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\SiaCode;
use App\Models\AlarmZone;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AlarmProcessor
{
    // Tolerancia en minutos para considerar una apertura/cierre como válida
    const SCHEDULE_TOLERANCE = 30; 

    public function process($accountNumber, $eventCode, $zoneOrUser, $rawData, $remoteIp)
    {
        Log::info("SIA: Procesando cuenta: $accountNumber | Evento: $eventCode");

        // 1. Definir tiempos (UTC para BD, Local para Lógica)
        $utcNow = now(); 
        $localNow = $utcNow->copy()->setTimezone('America/Caracas');

        // 2. Buscar el ID real de la cuenta
        $account = AlarmAccount::with('schedules')->where('account_number', $accountNumber)->first();

        if (!$account) {
            Log::warning("SIA: Cuenta $accountNumber no encontrada. Evento descartado.");
            return null;
        }

        // 3. Buscar descripción del código SIA
        $siaConfig = SiaCode::where('code', $eventCode)->first();
        $description = $siaConfig ? $siaConfig->description : 'Evento Desconocido';
        $priority = $siaConfig ? $siaConfig->priority : 3;

        // 4. Buscar nombre de zona (Opcional)
        $zoneName = '';
        if ($zoneOrUser) {
            $zone = AlarmZone::where('alarm_account_id', $account->id)
                             ->where('zone_number', $zoneOrUser)
                             ->first();
            if ($zone) $zoneName = " - " . $zone->name;
        }

        // 5. VERIFICACIÓN DE HORARIOS Y ESTADO ARMADO
        $scheduleNote = "";
        
        // Detectar tipo de evento (Simplificado para códigos estándar SIA)
        // OP/UA = Apertura (Disarmed), CL/CA = Cierre (Armed)
        $isOpening = in_array($eventCode, ['OP', 'UA', 'OR']);
        $isClosing = in_array($eventCode, ['CL', 'CA', 'CR']);

        if ($isOpening || $isClosing) {
            // A. Actualizar estado de armado
            $account->is_armed = $isClosing; // True si es cierre, False si es apertura

            // B. Validar Horario
            // Buscamos el horario para el día actual (0=Domingo, 6=Sábado)
            $daySchedule = $account->schedules
                                   ->where('day_of_week', $localNow->dayOfWeek)
                                   ->first();

            if ($daySchedule) {
                if ($isOpening && $daySchedule->open_time) {
                    $scheduleNote = $this->checkTimeCompliance(
                        $localNow, 
                        $daySchedule->open_time, 
                        'open'
                    );
                } elseif ($isClosing && $daySchedule->close_time) {
                    $scheduleNote = $this->checkTimeCompliance(
                        $localNow, 
                        $daySchedule->close_time, 
                        'close'
                    );
                }
            } else {
                // Si abre/cierra y NO tiene horario configurado para hoy
                $scheduleNote = " [FUERA DE HORARIO]"; 
                $priority = 1; // Prioridad alta
            }
        }

        // Si hubo violación de horario, lo agregamos a la descripción y subimos prioridad
        if ($scheduleNote) {
            $description .= $scheduleNote;
            $priority = 1; // Forzar atención del operador
        }

        // 6. Actualizar "última señal" de la cuenta (En UTC)
        $account->last_signal_at = $utcNow;
        $account->service_status = 'active';
        $account->save(); // Guardamos cambios de is_armed y timestamps

        // 7. GUARDAR EL EVENTO
        try {
            $event = AlarmEvent::create([
                'alarm_account_id' => $account->id,
                'event_code'       => $eventCode,
                'code'             => $eventCode,
                'description'      => $description . $zoneName,
                'zone'             => $zoneOrUser,
                'partition'        => '01',
                'raw_data'         => $rawData,
                'received_at'      => $utcNow, // Guardamos en UTC para auditoría
                'processed'        => ($priority <= 1) ? false : true // Si es prioridad alta, queda como NO procesado
            ]);

            return $event;

        } catch (\Exception $e) {
            Log::error("SIA Error DB: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida si la hora actual está dentro de la tolerancia del horario esperado.
     */
    private function checkTimeCompliance($currentLocalTime, $scheduledTimeString, $type)
    {
        try {
            // Crear objeto Carbon para la hora agendada (usando la fecha de hoy)
            $scheduledTime = Carbon::createFromFormat(
                'H:i:s', 
                $scheduledTimeString, 
                'America/Caracas'
            )->setDate(
                $currentLocalTime->year, 
                $currentLocalTime->month, 
                $currentLocalTime->day
            );

            // Calcular diferencia en minutos (absoluta)
            $diffMinutes = $currentLocalTime->diffInMinutes($scheduledTime);

            // Si está dentro de la tolerancia, todo bien
            if ($diffMinutes <= self::SCHEDULE_TOLERANCE) {
                return "";
            }

            // Si se pasa de la tolerancia, determinamos si es temprano o tarde
            if ($type === 'open') {
                // Apertura
                if ($currentLocalTime->gt($scheduledTime)) return " [APERTURA TARDIA ({$diffMinutes}m)]";
                else return " [APERTURA TEMPRANA ({$diffMinutes}m)]";
            } else {
                // Cierre
                if ($currentLocalTime->gt($scheduledTime)) return " [CIERRE TARDIO ({$diffMinutes}m)]";
                else return " [CIERRE TEMPRANO ({$diffMinutes}m)]";
            }

        } catch (\Exception $e) {
            Log::error("Error calculando horario: " . $e->getMessage());
            return "";
        }
    }
}