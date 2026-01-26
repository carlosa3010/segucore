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
        $this->info('Iniciando verificación de conectividad...');

        $accounts = AlarmAccount::where('is_active', true)
                                ->where('service_status', 'active')
                                ->get();

        // CAMBIO: Usamos FC (Fallo Comunicación) en lugar de FT
        $failureCode = 'FC'; 
        
        $siaConfig = SiaCode::where('code', $failureCode)->first();
        // Si no existe el código en BD, forzamos prioridad 3 (Alta/Ticket)
        $priority = $siaConfig ? $siaConfig->priority : 3;

        foreach ($accounts as $account) {
            if (!$account->last_signal_at) {
                continue;
            }

            $tolerance = 30; // Minutos de gracia
            $minutesSinceLastSignal = Carbon::now()->diffInMinutes($account->last_signal_at);
            $maxAllowedMinutes = $account->test_interval_minutes + $tolerance;

            // 1. Detección de Fallo
            if ($minutesSinceLastSignal > $maxAllowedMinutes) {
                
                // Evitar spam: solo alertar si no se ha alertado en las últimas 12h
                $spamProtectionTime = 720; 
                $lastFailure = $account->last_connection_failure_at;

                if (!$lastFailure || Carbon::now()->diffInMinutes($lastFailure) > $spamProtectionTime) {
                    
                    $this->triggerEvent($account, $failureCode, $minutesSinceLastSignal, $priority);
                    
                    $account->last_connection_failure_at = Carbon::now();
                    $account->save();
                    
                    $this->error("Alerta generada: {$account->account_number} (Offline {$minutesSinceLastSignal} min)");
                }
            } 
            // 2. Auto-Restauración (Opcional pero recomendada)
            else {
                // Si la cuenta tiene una marca de fallo pero ya está en tiempo, limpiamos la marca.
                // (Esto ocurre si entró una señal y AlarmProcessor actualizó last_signal_at)
                if ($account->last_connection_failure_at) {
                    $account->last_connection_failure_at = null;
                    $account->save();
                    $this->info("Cuenta recuperada: {$account->account_number}");
                }
            }
        }
    }

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
            'processed'        => ($priority <= 2) 
        ]);
    }
}