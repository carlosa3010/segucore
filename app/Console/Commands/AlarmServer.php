<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlarmProcessor;
use Illuminate\Support\Facades\Log;

class AlarmServer extends Command
{
    protected $signature = 'alarm:start {port=50000}';
    protected $description = 'Servidor SIA-DCS Multi-Cliente (Non-Blocking)';

    public function handle()
    {
        $port = $this->argument('port');
        $this->info("🎧 MONITOR-CORE: Servidor Multi-Cliente iniciado en puerto $port...");

        $server = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);
        if (!$server) {
            $this->error("❌ Error fatal: $errstr");
            return 1;
        }

        // Configurar servidor como no bloqueante
        stream_set_blocking($server, 0);

        // Array de clientes conectados
        $clients = [$server];

        while (true) {
            // Preparar arrays para stream_select
            $read = $clients;
            $write = null;
            $except = null;

            // Esperar actividad en los sockets (con timeout de 1s para no saturar CPU)
            if (stream_select($read, $write, $except, 1) < 1) {
                continue;
            }

            // Chequear si hay NUEVA conexión en el socket servidor
            if (in_array($server, $read)) {
                $newClient = stream_socket_accept($server);
                if ($newClient) {
                    stream_set_blocking($newClient, 0); // Cliente no bloqueante
                    $clients[] = $newClient;
                    $this->comment("🔌 Nueva conexión entrante: " . stream_socket_get_name($newClient, true));
                }
                // Quitar servidor del array de lectura para no procesarlo como cliente
                unset($read[array_search($server, $read)]);
            }

            // Procesar datos de CLIENTES existentes
            foreach ($read as $client) {
                // Leer datos
                $raw = @fread($client, 2048);
                $remoteIp = stream_socket_get_name($client, true) ?? 'Unknown';

                // Si fread devuelve false o vacío, el cliente se desconectó
                if ($raw === false || $raw === '') {
                    $this->line("XX Cliente desconectado: $remoteIp");
                    unset($clients[array_search($client, $clients)]);
                    @fclose($client);
                    continue;
                }

                $cleanData = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $raw));

                if (!empty($cleanData)) {
                    // --- LOGICA SIA ---
                    if (str_contains($cleanData, '"NULL"')) {
                        // Heartbeat
                        if (preg_match('/"NULL"(\d{4})/', $cleanData, $matches)) {
                            $this->sendAck($client, $matches[1], "NULL", "0000"); 
                        }
                    } elseif (str_contains($cleanData, '"SIA-DCS"')) {
                        // Evento Real
                        $this->processSiaEvent($client, $cleanData, $remoteIp);
                    }
                }
            }
        }
    }

    private function processSiaEvent($client, $data, $ip)
    {
        if (preg_match('/"SIA-DCS"(\d{4}).*?#(\w+)\[.*?\|N(.*?)\/(.*?)\]/', $data, $matches)) {
            $sequence = $matches[1];
            $account  = $matches[2];
            $details  = $matches[4];
            
            $code = substr($details, 0, 2);
            $zone = substr($details, 2);

            try {
                $processor = new AlarmProcessor();
                $event = $processor->process($account, $code, $zone, $data, $ip);
                
                $status = $event->processed ? "✅ AUTO" : "⚠️ ALERTA";
                $this->line("[$status] Cta: $account | Evento: $code | Zona: $zone");
                
                $this->sendAck($client, $sequence, "ACK", $account);
            } catch (\Exception $e) {
                Log::error("SIA Error: " . $e->getMessage());
                $this->sendAck($client, $sequence, "ACK", $account);
            }
        }
    }

    private function sendAck($client, $sequence, $type, $account)
    {
        $payload = "\"$type\"$sequence" . "L0#$account" . "[]";
        $len = strtoupper(str_pad(dechex(strlen($payload)), 4, '0', STR_PAD_LEFT));
        $packet = "\n\r0000$len$payload\n\r";
        @fwrite($client, $packet);
    }
}