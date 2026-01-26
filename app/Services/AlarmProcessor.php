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

    // Códigos que indican que un servicio se ha restaurado (Cierran el ciclo)
    const RESTORE_CODES = [
        'NR' => 'Red Celular Restaurada',
        'CU' => 'Línea Telefónica Restaurada',
        'YR' => 'Batería Sistema Restaurada',
        'XR' => 'Batería Repetidor Restaurada',
        'LR' => 'Canal Principal Restaurado',
        'DS' => 'Cámara Red Restaurada',
        'CV' => 'Supervisión BUS Restaurada',
        'MH' => 'Restauración Médica',
        'FH' => 'Restauración Incendio',
        'KH' => 'Restauración Fuego/Calor',
        'HH' => 'Restauración Coacción',
        'CH' => 'Restauración Pánico',
        'CK' => 'Restauración Robo',
        'TR' => 'Restauración Sabotaje',
        'YJ' => 'Sobrecorriente Restaurada',
        'YQ' => 'Sobrevoltaje Restaurado',
        'CQ' => 'Corto Salida Restaurado',
        'ER' => 'Expansor Restaurado',
        'YK' => 'Subida Reporte Restaurada',
        'FJ' => 'Fallo Sensor Restaurado',
        'QU' => 'Anulación Zona Restaurada',
        // 'AR' => 'Restauración AC', // Descomentar si tu panel usa AR para esto
    ];

    public function process($accountNumber, $eventCode, $zoneOrUser, $rawData, $remoteIp)
    {
        Log::info("SIA: Procesando cuenta: $accountNumber | Evento: $eventCode");

        // 1. Definir tiempos (UTC para BD, Local para Lógica)
        $utcNow = now(); 
        $localNow = $utcNow->copy()->setTimezone('America/Caracas');

        // 2. Buscar la cuenta con sus horarios
        $account = AlarmAccount::with('schedules')->where('account_number', $accountNumber)->first();

        if (!$account) {
            Log::warning("SIA: Cuenta $accountNumber no encontrada. Evento descartado.");
            return null;
        }

        // 3. Buscar configuración del código SIA
        $siaConfig = SiaCode::where('code', $eventCode)->first();
        $description = $siaConfig ? $siaConfig->description : 'Evento Desconocido';
        
        // Prioridad por defecto (3 = Alerta Técnica / Amarilla)
        $priority = $siaConfig ? $siaConfig->priority : 3;

        // 4. Buscar nombre de zona (Opcional)
        $zoneName = '';
        if ($zoneOrUser) {
            $zone = AlarmZone::where('alarm_account_id', $account->id)
                             ->where('zone_number', $zoneOrUser)
                             ->first();
            if ($zone) $zoneName = " - " . $zone->name;
        }

        // 5. LÓGICA DE RESTAURACIONES (AUTO-CIERRE)
        if (array_key_exists($eventCode, self::RESTORE_CODES)) {
            $description = "[RESTAURADO] " . $description;
            // Forzamos prioridad 1 (Baja/Informativa) para que se procese automático
            $priority = 1; 
        }

        // 6. LÓGICA DE HORARIOS Y ESTADO ARMADO
        $scheduleNote = "";
        
        // Detectar tipo de evento SIA estándar
        $isOpening = in_array($eventCode, ['OP', 'UA', 'OR']); // Aperturas
        $isClosing = in_array($eventCode, ['CL', 'CA', 'CR']); // Cierres

        if ($isOpening || $isClosing) {
            // A. Actualizar estado de armado en la cuenta
            $account->is_armed = $isClosing; 

            // B. Validar Horario
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
                // Si hay movimiento de armado/desarmado y NO hay horario para hoy
                $scheduleNote = " [FUERA DE HORARIO NO CONFIGURADO]"; 
                $priority = 4; // Prioridad Alta (Alerta)
            }
        }

        // Si hubo violación de horario, lo agregamos y subimos prioridad
        if ($scheduleNote) {
            $description .= $scheduleNote;
            // Si era una apertura/cierre normal (Prio 2), ahora es una Alerta (Prio 4)
            if ($priority < 3) {
                $priority = 4;
            }
        }

        // 7. Actualizar "última señal" de la cuenta
        
        // --- INICIO DEBUG ---
        Log::info("DEBUG MONITOR: Intentando actualizar last_signal_at para cuenta: {$account->account_number}");
        Log::info("DEBUG MONITOR: Valor anterior: " . ($account->last_signal_at ?? 'NULL'));
        Log::info("DEBUG MONITOR: Nuevo valor a guardar: " . $utcNow);
        // --- FIN DEBUG ---

        $account->last_signal_at = $utcNow;
        $account->service_status = 'active';
        
        // Si quieres limpiar el fallo inmediatamente al recibir señal (Recomendado):
        $account->last_connection_failure_at = null; 

        $saved = $account->save(); 

        // --- INICIO DEBUG ---
        if ($saved) {
            Log::info("DEBUG MONITOR: ¡Guardado EXITOSO en Base de Datos!");
        } else {
            Log::error("DEBUG MONITOR: ALERTA - El método save() devolvió false.");
        }
        // --- FIN DEBUG ---

        // 8. GUARDAR EL EVENTO
        try {
            // LÓGICA DE PROCESADO:
            // Si Priority <= 2 (Tests, Armados normales, Restauraciones) -> processed = true (Historial)
            // Si Priority >= 3 (Fallo AC, Robo, Pánico, Horario Tarde) -> processed = false (TICKET)
            $isProcessed = ($priority <= 2);

            $event = AlarmEvent::create([
                'alarm_account_id' => $account->id,
                'account_number'   => $accountNumber,
                'event_code'       => $eventCode,
                'code'             => $eventCode,
                'description'      => $description . $zoneName,
                'zone'             => $zoneOrUser,
                'partition'        => '01',
                'raw_data'         => $rawData,
                'received_at'      => $utcNow, // Siempre UTC para auditoría
                'processed'        => $isProcessed
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

            // Si se pasa de la tolerancia, determinamos el mensaje
            if ($type === 'open') {
                if ($currentLocalTime->gt($scheduledTime)) return " [APERTURA TARDIA ({$diffMinutes}m)]";
                else return " [APERTURA TEMPRANA ({$diffMinutes}m)]";
            } else {
                if ($currentLocalTime->gt($scheduledTime)) return " [CIERRE TARDIO ({$diffMinutes}m)]";
                else return " [CIERRE TEMPRANO ({$diffMinutes}m)]";
            }

        } catch (\Exception $e) {
            Log::error("Error calculando horario: " . $e->getMessage());
            return "";
        }
    }
}