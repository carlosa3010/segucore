<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlarmAccount;
use App\Models\AlarmEvent;
use App\Models\SiaCode;
use Carbon\Carbon;

class CheckConnectionStatus extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'segucore:check-connections';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Verifica si las cuentas de alarma han reportado dentro de su intervalo esperado';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('--- Iniciando verificación de conectividad ---');

        // 1. Obtener cuentas activas y con servicio activo
        $accounts = AlarmAccount::where('is_active', true)
                                ->where('service_status', 'active')
                                ->get();

        $this->info("Cuentas encontradas para analizar: " . $accounts->count());

        // Configuración del código de fallo
        $failureCode = 'FC'; // Fallo de Comunicación
        
        $siaConfig = SiaCode::where('code', $failureCode)->first();
        // Si no existe configuración, asumimos prioridad 3 (Alta/Ticket)
        $priority = $siaConfig ? $siaConfig->priority : 3;

        foreach ($accounts as $account) {
            $this->line("Analizando cuenta: {$account->account_number}...");

            // Validación: Si nunca ha reportado, no podemos calcular diferencia
            if (!$account->last_signal_at) {
                $this->warn("   -> Saltada: No tiene fecha de 'last_signal_at' registrada.");
                continue;
            }

            // Configuración de tiempos
            $tolerance = 30; // Minutos de gracia extras al intervalo
            $interval = $account->test_interval_minutes ?? 1440; // Default 24h si es null
            
            // CORRECCIÓN CRÍTICA: Usamos abs() para garantizar número positivo
            // Carbon::now()->diffInMinutes($date, false) devuelve negativo si $date es pasado
            $minutesSinceLastSignal = abs(Carbon::now()->diffInMinutes($account->last_signal_at, false));
            
            $maxAllowedMinutes = $interval + $tolerance;

            $this->line("   -> Tiempo sin señal: {$minutesSinceLastSignal} min (Máximo permitido: {$maxAllowedMinutes} min)");

            // --- LÓGICA DE DETECCIÓN ---

            // CASO 1: FALLO DETECTADO (Tiempo excedido)
            if ($minutesSinceLastSignal > $maxAllowedMinutes) {
                
                // Protección contra Spam: Solo alertar si no se ha alertado en las últimas 12h (720 min)
                $spamProtectionTime = 720; 
                $lastFailure = $account->last_connection_failure_at;
                
                // Calculamos hace cuánto fue el último fallo para el log
                // Usamos abs() aquí también por seguridad
                $minutesSinceFailure = $lastFailure ? abs(Carbon::now()->diffInMinutes($lastFailure, false)) : 'N/A';

                if (!$lastFailure || $minutesSinceFailure > $spamProtectionTime) {
                    
                    // DISPARAR EVENTO
                    $this->triggerEvent($account, $failureCode, $minutesSinceLastSignal, $priority);
                    
                    // Actualizar marca de tiempo para evitar duplicados inmediatos
                    $account->last_connection_failure_at = Carbon::now();
                    $account->save();
                    
                    $this->error("   -> ALERTA GENERADA (Offline hace " . round($minutesSinceLastSignal/60, 1) . " horas).");
                } else {
                    $this->comment("   -> Alerta omitida por protección de spam (Última alerta hace {$minutesSinceFailure} min).");
                }
            } 
            
            // CASO 2: RECUPERACIÓN (Tiempo OK)
            else {
                // Si la cuenta tenía una marca de fallo previa pero ahora está bien (recibió señal)
                if ($account->last_connection_failure_at) {
                    $account->last_connection_failure_at = null;
                    $account->save();
                    $this->info("   -> Cuenta RECUPERADA (Marca de fallo limpiada).");
                } else {
                    $this->info("   -> Estado OK.");
                }
            }
        }

        $this->info('--- Verificación completada ---');
    }

    /**
     * Crea el evento en la tabla alarm_events
     */
    private function triggerEvent($account, $code, $minutesSilent, $priority)
    {
        $hours = round($minutesSilent / 60, 1);
        
        AlarmEvent::create([
            'alarm_account_id' => $account->id,
            'event_code'       => $code,
            'code'             => $code,
            'description'      => "FALLO DE COMUNICACIÓN: Sin señal por {$hours} horas.",
            'partition'        => '00',
            'raw_data'         => 'SYSTEM_AUTO_CHECK',
            'received_at'      => Carbon::now(),
            // Si priority es <= 2 es informativo (processed=true). 
            // Si es >= 3 es alerta (processed=false) para que genere Ticket.
            'processed'        => ($priority <= 2) 
        ]);
    }
}