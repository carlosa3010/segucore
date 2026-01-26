<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlarmAccount;
use App\Models\AlarmEvent;
use App\Models\SiaCode;
use Carbon\Carbon;

class CheckConnectionStatus extends Command
{
    protected $signature = 'segucore:check-connections';
    protected $description = 'Verifica si las cuentas de alarma han reportado dentro de su intervalo esperado';

    public function handle()
    {
        $this->info('Iniciando verificación de conectividad de alarmas...');

        // 1. Obtener cuentas activas que tengan un plan de servicio activo
        $accounts = AlarmAccount::where('is_active', true)
                                ->where('service_status', 'active')
                                ->get();

        $failureCode = 'FT'; // Código interno para Fallo de Test
        // Buscamos la config del código para saber su prioridad
        $siaConfig = SiaCode::where('code', $failureCode)->first();
        $priority = $siaConfig ? $siaConfig->priority : 3;

        foreach ($accounts as $account) {
            // Si nunca ha reportado, saltamos (o podrías marcarlo como fallo inicial)
            if (!$account->last_signal_at) {
                continue;
            }

            // 2. Calcular tiempo límite
            // Damos una tolerancia de 30 minutos extra al intervalo configurado
            $tolerance = 30; 
            $minutesSinceLastSignal = Carbon::now()->diffInMinutes($account->last_signal_at);
            $maxAllowedMinutes = $account->test_interval_minutes + $tolerance;

            if ($minutesSinceLastSignal > $maxAllowedMinutes) {
                // 3. La cuenta está en fallo. Verificamos si ya alertamos recientemente.
                // Si ya generamos una alerta en el último periodo (ej. últimas 12 horas), no repetimos.
                $spamProtectionTime = 720; // 12 horas
                $lastFailure = $account->last_connection_failure_at;

                if (!$lastFailure || Carbon::now()->diffInMinutes($lastFailure) > $spamProtectionTime) {
                    
                    $this->triggerEvent($account, $failureCode, $minutesSinceLastSignal, $priority);
                    
                    // Actualizamos la marca de tiempo del fallo para evitar duplicados
                    $account->last_connection_failure_at = Carbon::now();
                    $account->save();
                    
                    $this->error("Alerta generada para cuenta: {$account->account_number} (Sin señal por {$minutesSinceLastSignal} min)");
                }
            } else {
                // Si la cuenta recuperó conexión, podríamos limpiar la marca de fallo (opcional)
                if ($account->last_connection_failure_at) {
                    $account->last_connection_failure_at = null;
                    $account->save();
                }
            }
        }

        $this->info('Verificación completada.');
    }

    private function triggerEvent($account, $code, $minutesSilent, $priority)
    {
        // Convertimos minutos a horas para que sea legible
        $hours = round($minutesSilent / 60, 1);
        
        // Creamos el evento. 
        // Nota: 'processed' = false indica que requiere atención (genera Incidente según tu lógica de AlarmProcessor)
        AlarmEvent::create([
            'alarm_account_id' => $account->id,
            'event_code'       => $code,
            'code'             => $code,
            'description'      => "FALLO DE COMUNICACIÓN: Sin señal por {$hours} horas.",
            'zone'             => null,
            'partition'        => '00',
            'raw_data'         => 'SYSTEM_GENERATED_EVENT',
            'received_at'      => Carbon::now(),
            'processed'        => ($priority <= 2) // Si es prioridad alta (3), processed es false -> Genera Ticket
        ]);
    }
}