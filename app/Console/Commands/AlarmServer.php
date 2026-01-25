<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlarmProcessor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AlarmServer extends Command
{
    protected $signature = 'alarm:start {port=50000}';
    protected $description = 'Servidor SIA-DCS Multi-Cliente Mejorado';

    public function handle()
    {
        // Forzar salida inmediata en consola (sin buffer)
        ob_implicit_flush(true);

        $port = $this->argument('port');
        $this->info("🎧 MONITOR-CORE: Servidor Iniciado en puerto $port...");

        $server = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);
        if (!$server) {
            $this->error("❌ Error fatal: $errstr");
            return 1;
        }

        stream_set_blocking($server, 0);
        $clients = [$server];
        $lastDbCheck = time();

        while (true) {
            // 1. Keep-Alive de Base de Datos
            if (time() - $lastDbCheck > 60) {
                try {
                    DB::connection()->getPdo();
                } catch (\Exception $e) {
                    $this->warn("⚠️ Reconectando DB...");
                    DB::reconnect();
                }
                $lastDbCheck = time();
            }

            // 2. Gestión de Sockets
            $read = $clients;
            $write = null;
            $except = null;

            if (stream_select($read, $write, $except, 1) < 1) {
                continue;
            }

            // 3. Nuevas Conexiones
            if (in_array($server, $read)) {
                $newClient = stream_socket_accept($server);
                if ($newClient) {
                    stream_set_blocking($newClient, 0);
                    $clients[] = $newClient;
                    $this->comment("🔌 Nueva conexión: " . stream_socket_get_name($newClient, true));
                }
                unset($read[array_search($server, $read)]);
            }

            // 4. Procesar Datos
            foreach ($read as $client) {
                $raw = @fread($client, 2048);
                $remoteIp = stream_socket_get_name($client, true) ?? 'Unknown';

                if ($raw === false || $raw === '') {
                    // Cliente desconectado
                    unset($clients[array_search($client, $clients)]);
                    @fclose($client);
                    continue;
                }

                $cleanData = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $raw));

                if (!empty($cleanData)) {
                    // Log crudo para depuración (opcional, comentar en producción)
                    // $this->line("RAW < $cleanData");

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
        // --- CORRECCIÓN 1: REGEX UNIVERSAL ---
        // Captura cualquier cosa entre corchetes [...] que termine en /CCZZ]
        // CC = Código (2 letras), ZZ = Zona (chars restantes)
        // Ejemplo captura: [#1234|Nri1/BA01] -> Account:1234, Data:BA01
        
        if (preg_match('/"SIA-DCS"(\d{4}).*?#(\w+)\[.*?\/(\w+)\]/', $data, $matches)) {
            $sequence = $matches[1];
            $account  = $matches[2];
            $fullCode = $matches[3]; // Ej: BA01
            
            $code = substr($fullCode, 0, 2); // BA
            $zone = substr($fullCode, 2);    // 01

            try {
                // --- CORRECCIÓN 2: MÉTODO DB CORRECTO ---
                // Eliminado 'reconnectIfMissing' que no existe
                try {
                    DB::connection()->getPdo();
                } catch (\Exception $e) {
                    DB::reconnect();
                }

                $processor = new AlarmProcessor();
                $event = $processor->process($account, $code, $zone, $data, $ip);
                
                if ($event) {
                    $status = $event->processed ? "✅ AUTO" : "⚠️ ALERTA";
                    $this->info("[$status] Cta: $account | Evento: $code | Zona: $zone");
                } else {
                    $this->error("❌ Cta: $account | No encontrada en DB");
                }
                
                $this->sendAck($client, $sequence, "ACK", $account);

            } catch (\Exception $e) {
                Log::error("SIA Error: " . $e->getMessage());
                $this->error("🔥 EXCEPCIÓN: " . $e->getMessage()); // Verlo en consola
                $this->sendAck($client, $sequence, "ACK", $account);
            }
        } else {
            // --- CORRECCIÓN 3: LOG DE RECHAZO ---
            // Si la regex falla, avisar para saber por qué
            $this->warn("❓ Formato desconocido: $data");
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