<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TraccarApiService
{
    protected $url;
    protected $user;
    protected $pass;

    public function __construct()
    {
        $this->url = config('services.traccar.url'); // Ej: http://64.23.146.136:8082/api
        $this->user = config('services.traccar.user');
        $this->pass = config('services.traccar.pass');
    }

    /**
     * Sincronizar (Crear o Actualizar) Dispositivo
     */
    public function syncDevice($name, $uniqueId, $phone, $model)
    {
        // 1. Buscar si existe
        $search = Http::withBasicAuth($this->user, $this->pass)
            ->get("{$this->url}/devices", ['uniqueId' => $uniqueId]);

        if ($search->successful() && count($search->json()) > 0) {
            // ACTUALIZAR
            $deviceId = $search->json()[0]['id'];
            Http::withBasicAuth($this->user, $this->pass)
                ->put("{$this->url}/devices/{$deviceId}", [
                    'id' => $deviceId,
                    'name' => $name,
                    'uniqueId' => $uniqueId,
                    'phone' => $phone,
                    'model' => $model,
                ]);
            return $deviceId;
        } else {
            // CREAR NUEVO
            $response = Http::withBasicAuth($this->user, $this->pass)
                ->post("{$this->url}/devices", [
                    'name' => $name,
                    'uniqueId' => $uniqueId,
                    'phone' => $phone,
                    'model' => $model,
                ]);
            
            if ($response->successful()) {
                return $response->json()['id'];
            }
        }

        return null;
    }

    /**
     * Eliminar Dispositivo
     */
    public function deleteDevice($traccarId)
    {
        Http::withBasicAuth($this->user, $this->pass)
            ->delete("{$this->url}/devices/{$traccarId}");
    }

    /**
     * Enviar Comando (Corte de motor, etc)
     */
    public function sendCommand($deviceId, $type)
    {
        $cmdData = [
            'deviceId' => $deviceId,
            'type' => $type // 'engineStop' o 'engineResume'
        ];

        // Mapeo de comandos personalizados si usas computed attributes en Traccar
        // O si usas comandos estándar:
        /*
        if ($type === 'engineStop') {
            $cmdData['type'] = 'engineStop'; 
        }
        */

        $response = Http::withBasicAuth($this->user, $this->pass)
            ->post("{$this->url}/commands/send", $cmdData);

        return $response->successful();
    }

    /**
     * Crear Geocerca (NUEVO - Soluciona el error)
     * @param string $name Nombre de la zona
     * @param string $area Cadena WKT (POLYGON((...)))
     */
    public function createGeofence($name, $area, $description = '')
    {
        $response = Http::withBasicAuth($this->user, $this->pass)
            ->post("{$this->url}/geofences", [
                'name' => $name,
                'area' => $area,
                'description' => $description,
            ]);

        if ($response->successful()) {
            return $response->json()['id']; // Retorna el ID creado en Traccar
        }

        // Loguear error para debug
        Log::error('Error creando geocerca en Traccar: ' . $response->body());
        
        throw new \Exception("No se pudo sincronizar la geocerca con Traccar.");
    }
}