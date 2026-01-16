<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\AlarmEvent;

class AlarmServer extends Command
{
    protected $signature = 'alarm:start {port=50000}';
    protected $description = 'Servidor SIA-DCS Híbrido (Soporta Heartbeat NULL de Hikvision)';

    public function handle()
    {
        $port = $this->argument('port');
        $this->info("🛡️  SIA-CORE V3: Listener con soporte Heartbeat iniciado en puerto $port...");

        $server = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);

        if (!$server) {
            $this->error("❌ Error fatal: $errstr ($errno)");
            return 1;
        }

        while (true) {
            $client = @stream_socket_accept($server, -1);

            if ($client) {
                $remoteIp = stream_socket_get_name($client, true);
                
                // Mantenemos la conexión viva
                while (!feof($client)) {
                    $raw = fread($client, 4096);

                    if (!empty($raw)) {
                        // Limpieza básica (quitamos caracteres nulos raros pero dejamos los imprimibles)
                        $cleanData = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $raw));
                        
                        // ---------------------------------------------------------
                        // CASO 1: ES UN HEARTBEAT (Hikvision preguntando "¿Estás ahí?")
                        // Formato típico: "NULL"0000L0#0000[#0000|0000 00 000]
                        // ---------------------------------------------------------
                        if (str_contains($cleanData, '"NULL"')) {
                            // Extraemos la secuencia del heartbeat
                            if (preg_match('/"NULL"(\d{4})/', $cleanData, $matches)) {
                                $sequence = $matches[1];
                                $this->comment("💓 Heartbeat recibido (Seq: $sequence) - Manteniendo conexión...");
                                
                                // Respondemos ACK al Heartbeat para que no corte
                                $this->sendAck($client, $sequence, "NULL", "0000"); 
                            }
                        }

                        // ---------------------------------------------------------
                        // CASO 2: ES UNA ALARMA REAL (SIA-DCS)
                        // ---------------------------------------------------------
                        elseif (str_contains($cleanData, '"SIA-DCS"')) {
                            $siaData = $this->parseSiaMessage($cleanData);

                            if ($siaData) {
                                $this->info("🔔 ALARMA REAL: " . $siaData['event_code'] . " - Cuenta: " . $siaData['account']);
                                
                                // Guardar en DB
                                try {
                                    AlarmEvent::create([
                                        'account_number' => $siaData['account'],
                                        'event_code'     => $siaData['event_code'],
                                        'event_type'     => $siaData['event_type'],
                                        'zone'           => $siaData['zone'],
                                        'ip_address'     => $remoteIp,
                                        'raw_data'       => $cleanData,
                                        'received_at'    => now(),
                                        'processed'      => false
                                    ]);
                                    $this->info("💾 Guardado DB.");
                                } catch (\Exception $e) {
                                    Log::error("DB Error: " . $e->getMessage());
                                }

                                // Respondemos ACK a la Alarma
                                $this->sendAck($client, $siaData['sequence'], "ACK", $siaData['account']);
                            }
                        } 
                        
                        // Logueamos basura si no entendemos nada (Debugging)
                        else {
                            if (strlen($cleanData) > 5) {
                                // Log::warning("Trama desconocida: $cleanData");
                            }
                        }

                    } else {
                        usleep(100000); // 100ms pausa
                    }
                }
                fclose($client);
            }
        }
    }

    /**
     * Envía el ACK con el formato correcto incluyendo CRC ficticio
     */
    private function sendAck($client, $sequence, $type = "ACK", $account = "0000")
    {
        // Construimos el payload de respuesta
        // Si es heartbeat tipo NULL, respondemos NULL o ACK. Hikvision suele aceptar ACK.
        // Formato estándar respuesta: "ACK"SECUENCIAL0#CUENTA[]
        
        // NOTA: Para el mensaje NULL, la cuenta suele ser 0000.
        $ackPayload = "\"$type\"" . $sequence . "L0#" . $account . "[]";
        
        // Calculamos longitud (Length) en Hexa (4 digitos)
        $length = strtoupper(str_pad(dechex(strlen($ackPayload)), 4, '0', STR_PAD_LEFT));
        
        // Calculamos CRC (usamos 0000 por defecto ya que calcular CRC16 real en PHP puro es lento y Hikvision acepta 0000 a veces)
        // Pero para engañar al receiver ponemos un CRC nulo válido sintácticamente.
        $crc = "0000"; 
        
        // Estructura Final: <LF> CRC LENGTH PAYLOAD <CR>
        // Importante: Hikvision IP Receiver espera a veces solo el payload si no hay encryption.
        // Probamos con el formato completo con cabecera nula.
        
        $response = "\n\r" . $crc . $length . $ackPayload . "\n\r";
        
        @fwrite($client, $response);
    }

    private function parseSiaMessage($string)
    {
        // Regex mejorado para capturar SIA-DCS incluso si hay basura antes
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
