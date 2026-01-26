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
        $this->info('--- Iniciando verificación de conectividad ---');

        // Solo cuentas activas y monitoreadas
        $accounts = AlarmAccount::where('is_active', true)
                                ->where('service_status', 'active')
                                ->get();

        $this->info("Cuentas encontradas para analizar: " . $accounts->count());

        $failureCode = 'FC'; // Fallo de Comunicación
        $siaConfig = SiaCode::where('code', $failureCode)->first();
        // Obtenemos la prioridad (3) para decidir si requiere atención
        $priority = $siaConfig ? $siaConfig->priority : 3;

        foreach ($accounts as $account) {
            // Si nunca ha reportado, no podemos calcular tiempos
            if (!$account->last_signal_at) {
                continue;
            }

            // Configuración de tiempos (Default 24h + 30min gracia)
            $tolerance = 30;
            $interval = $account->test_interval_minutes ?? 1440;
            
            // Usamos abs() para obtener siempre un número positivo de minutos
            $minutesSinceLastSignal = abs(Carbon::now()->diffInMinutes($account->last_signal_at, false));
            $maxAllowedMinutes = $interval + $tolerance;

            // --- LÓGICA DE DETECCIÓN ---
            
            // CASO 1: FALLO (Tiempo excedido)
            if ($minutesSinceLastSignal > $maxAllowedMinutes) {
                
                // Protección Spam: No repetir alerta en 12h (720 min)
                $spamProtectionTime = 720; 
                $lastFailure = $account->last_connection_failure_at;
                $minutesSinceFailure = $lastFailure ? abs(Carbon::now()->diffInMinutes($lastFailure, false)) : 'N/A';

                if (!$lastFailure || $minutesSinceFailure > $spamProtectionTime) {
                    
                    // DISPARAR EVENTO
                    $this->triggerEvent($account, $failureCode, $minutesSinceLastSignal, $priority);
                    
                    // Marcar que ya avisamos
                    $account->last_connection_failure_at = Carbon::now();
                    $account->save();
                    
                    $this->error("   -> ALERTA GENERADA: {$account->account_number} (Offline hace " . round($minutesSinceLastSignal/60, 1) . " horas).");
                } 
            } 
            // CASO 2: RECUPERACIÓN (Si estaba fallando y ya reportó)
            else {
                if ($account->last_connection_failure_at) {
                    $account->last_connection_failure_at = null;
                    $account->save();
                    $this->info("   -> Cuenta RECUPERADA: {$account->account_number}");
                }
            }
        }
        $this->info('--- Verificación completada ---');
    }

    private function triggerEvent($account, $code, $minutesSilent, $priority)
    {
        $hours = round($minutesSilent / 60, 1);
        
        AlarmEvent::create([
            'alarm_account_id' => $account->id,
            
            // IMPORTANTE: Guardamos el número de cuenta texto para la consola
            'account_number'   => $account->account_number, 
            
            'event_code'       => $code,
            'code'             => $code,
            'description'      => "FALLO DE COMUNICACIÓN: Sin señal por {$hours} horas.",
            'partition'        => '00',
            'raw_data'         => 'SYSTEM_AUTO_CHECK',
            'received_at'      => Carbon::now(),
            
            // Aquí usamos la prioridad: Si es 3 (High), processed es false -> Genera Ticket
            'processed'        => ($priority <= 2) 
        ]);
    }
}