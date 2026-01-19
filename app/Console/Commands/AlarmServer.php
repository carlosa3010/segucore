<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\AlarmEvent;
use App\Models\SiaCode;
use App\Services\AlarmProcessor;

class AlarmServer extends Command
{
    protected $signature = 'alarm:start {port=50000}';
    protected $description = 'Servidor SIA-DCS Híbrido con Autoprocesamiento por Prioridad';

    public function handle()
    {
        $port = $this->argument('port');
        $this->info("🛡️  SIA-CORE V3: Listener iniciado en puerto $port...");

        $server = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);

        if (!$server) {
            $this->error("❌ Error fatal: $errstr ($errno)");
            return 1;
        }

        while (true) {
            $client = @stream_socket_accept($server, -1);

            if ($client) {
                $remoteIp = stream_socket_get_name($client, true);
                
                while (!feof($client)) {
                    $raw = fread($client, 4096);

                    if (!empty($raw)) {
                        // Limpieza de caracteres no imprimibles
                        $cleanData = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $raw));
                        
                        // --- CASO 1: HEARTBEAT (NULL) ---
                        if (str_contains($cleanData, '"NULL"')) {
                            if (preg_match('/"NULL"(\d{4})/', $cleanData, $matches)) {
                                $sequence = $matches[1];
                                $this->comment("💓 Heartbeat recibido (Seq: $sequence) de $remoteIp");
                                $this->sendAck($client, $sequence, "NULL", "0000"); 
                            }
                        }

                        // --- CASO 2: ALARMA REAL (SIA-DCS) ---
                        elseif (str_contains($cleanData, '"SIA-DCS"')) {
                            $siaData = $this->parseSiaMessage($cleanData);

                            if ($siaData) {
                                // LÓGICA DE AUTOPROCESAMIENTO
                                // Buscamos el código en los 268 cargados para ver su prioridad
                                $siaConfig = SiaCode::where('code', $siaData['event_code'])->first();
                                
                                // Si la prioridad es 0 o 1, se autoprocesa (Test/Info)
                                $isAutoProcess = ($siaConfig && $siaConfig->priority <= 1);
                                $priorityLabel = $siaConfig ? " [Prioridad: {$siaConfig->priority}]" : " [Sin Clasificar]";

                                $this->info("🔔 EVENTO: " . $siaData['event_code'] . " - Cuenta: " . $siaData['account'] . $priorityLabel);
                                
                                try {
                                    AlarmEvent::create([
                                        'account_number' => $siaData['account'],
                                        'event_code'     => $siaData['event_code'],
                                        'event_type'     => $siaData['event_type'],
                                        'zone'           => $siaData['zone'],
                                        'ip_address'     => $remoteIp,
                                        'raw_data'       => $cleanData,
                                        'received_at'    => now(),
                                        'processed'      => $isAutoProcess,
                                        'processed_at'   => $isAutoProcess ? now() : null,
                                    ]);
                                    
                                    $msg = $isAutoProcess ? "💾 Guardado y Autoprocesado (Oculto en Monitor)." : "💾 Guardado Pendiente (Visible en Monitor).";
                                    $isAutoProcess ? $this->comment($msg) : $this->warn($msg);

                                } catch (\Exception $e) {
                                    Log::error("Error al guardar evento SIA: " . $e->getMessage());
                                    $this->error("❌ Error DB: " . $e->getMessage());
                                }

                                // Respondemos ACK
                                $this->sendAck($client, $siaData['sequence'], "ACK", $siaData['account']);
                            }
                        } 
                    } else {
                        usleep(100000); 
                    }
                }
                fclose($client);
            }
        }
    }

    private function sendAck($client, $sequence, $type = "ACK", $account = "0000")
    {
        $ackPayload = "\"$type\"" . $sequence . "L0#" . $account . "[]";
        $length = strtoupper(str_pad(dechex(strlen($ackPayload)), 4, '0', STR_PAD_LEFT));
        $crc = "0000"; 
        
        $response = "\n\r" . $crc . $length . $ackPayload . "\n\r";
        @fwrite($client, $response);
    }

    private function parseSiaMessage($string)
    {
        // Captura secuencia, cuenta, tipo y código/zona
        $pattern = '/"SIA-DCS"(\d{4})L0#(\w+)\[.*?\|N(.*?)\/(.*?)\]/';
        
        if (preg_match($pattern, $string, $matches)) {
            return [
                'sequence'   => $matches[1], 
                'account'    => $matches[2], 
                'event_type' => $matches[3], 
                'event_code' => $matches[4], 
                'zone'       => $matches[4] 
            ];
        }
        return null;
    }
}