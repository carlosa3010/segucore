<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlarmProcessor; // Invocamos al "Cerebro"
use Illuminate\Support\Facades\Log;

class AlarmServer extends Command
{
    protected $signature = 'alarm:start {port=50000}';
    protected $description = 'Servidor de Recepción SIA-DCS V4 (Arquitectura Desacoplada)';

    public function handle()
    {
        $port = $this->argument('port');
        $this->info("🎧 MONITOR-CORE: Escuchando señales en puerto $port...");

        // Iniciar Socket TCP
        $server = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);

        if (!$server) {
            $this->error("❌ Error fatal al iniciar socket: $errstr ($errno)");
            return 1;
        }

        while (true) {
            // Aceptar conexión entrante (Hikvision / Panel)
            $client = @stream_socket_accept($server, -1);

            if ($client) {
                $remoteIp = stream_socket_get_name($client, true);
                
                // Leer flujo de datos
                while (!feof($client)) {
                    $raw = fread($client, 1024);

                    if (!empty($raw)) {
                        // 1. Limpieza de trama (Quitar caracteres no imprimibles)
                        $cleanData = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $raw));

                        // -----------------------------------------------------
                        // CASO A: HEARTBEAT (NULL) - Solo mantener vivo
                        // -----------------------------------------------------
                        if (str_contains($cleanData, '"NULL"')) {
                            if (preg_match('/"NULL"(\d{4})/', $cleanData, $matches)) {
                                $sequence = $matches[1];
                                // Respondemos ACK sin procesar nada más
                                $this->sendAck($client, $sequence, "NULL", "0000"); 
                            }
                        }

                        // -----------------------------------------------------
                        // CASO B: ALARMA / EVENTO REAL (SIA-DCS)
                        // -----------------------------------------------------
                        elseif (str_contains($cleanData, '"SIA-DCS"')) {
                            $this->processSiaEvent($client, $cleanData, $remoteIp);
                        }
                    } else {
                        usleep(50000); // Pequeña pausa para no saturar CPU
                    }
                }
                fclose($client);
            }
        }
    }

    /**
     * Procesa el evento SIA delegando al AlarmProcessor
     */
    private function processSiaEvent($client, $data, $ip)
    {
        // REGEX MEJORADO: Captura flexible de tramas SIA
        // Estructura típica: "SIA-DCS"0001L0#1234[#1234|Nri1/BA01]
        // Grupo 1: Secuencia (0001)
        // Grupo 2: Cuenta (1234)
        // Grupo 3: Info Partición/Tipo (ri1)
        // Grupo 4: Código + Zona (BA01)
        
        if (preg_match('/"SIA-DCS"(\d{4}).*?#(\w+)\[.*?\|N(.*?)\/(.*?)\]/', $data, $matches)) {
            $sequence = $matches[1];
            $account  = $matches[2];
            // $type  = $matches[3]; // Info de partición (ej: ri1)
            $details  = $matches[4]; // Info de evento (ej: BA01)

            // Separar Código (2 letras) de Zona (resto)
            $code = substr($details, 0, 2); // BA
            $zone = substr($details, 2);    // 01

            try {
                // LLAMAMOS AL CEREBRO (AlarmProcessor)
                // Esto busca la cuenta, valida horarios, crea logs y alarmas
                $processor = new AlarmProcessor();
                $event = $processor->process($account, $code, $zone, $data, $ip);

                // Feedback visual en consola
                $status = $event->processed ? "✅ AUTO" : "⚠️ ALERTA";
                $this->line("[$status] Cta: $account | Evento: $code | Zona: $zone");

                // RESPONDER ACK (Confirmación de recepción)
                $this->sendAck($client, $sequence, "ACK", $account);

            } catch (\Exception $e) {
                Log::error("Error procesando SIA: " . $e->getMessage());
                $this->error("Error procesando: " . $e->getMessage());
                // Aun con error interno, respondemos ACK para que el panel no reintente infinitamente
                $this->sendAck($client, $sequence, "ACK", $account);
            }
        } else {
            $this->warn("Trama desconocida o malformada: $data");
        }
    }

    /**
     * Generar respuesta SIA estándar
     */
    private function sendAck($client, $sequence, $type, $account)
    {
        // Protocolo SIA: Longitud + "ACK" + Secuencia + Cuenta
        $payload = "\"$type\"$sequence" . "L0#$account" . "[]";
        
        // Calcular longitud en HEX (4 dígitos)
        $len = strtoupper(str_pad(dechex(strlen($payload)), 4, '0', STR_PAD_LEFT));
        
        // CRC (0000 es aceptado por la mayoría de receptores IP modernos)
        $crc = "0000"; 

        $packet = "\n\r$crc$len$payload\n\r";
        @fwrite($client, $packet);
    }
}