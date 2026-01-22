<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TraccarApiService
{
    protected $baseUrl;
    protected $user;
    protected $password;

    public function __construct()
    {
        // Usa las claves que confirmaste que funcionan en tu entorno
        $this->baseUrl = config('services.traccar.base_url');
        $this->user = config('services.traccar.user');
        $this->password = config('services.traccar.password');
    }

    /**
     * Helper para peticiones HTTP con Autenticación
     */
    private function client()
    {
        return Http::withBasicAuth($this->user, $this->password)
            ->acceptJson()
            ->timeout(10); // 10 segundos timeout
    }

    /**
     * 1. Sincronizar/Crear Dispositivo
     */
    public function syncDevice($name, $uniqueId, $phone = null, $model = null)
    {
        // Primero buscamos si ya existe por uniqueId
        $search = $this->client()->get("{$this->baseUrl}/devices", ['uniqueId' => $uniqueId]);
        
        if ($search->successful() && count($search->json()) > 0) {
            // Si existe, actualizamos
            $traccarId = $search->json()[0]['id'];
            return $this->updateDevice($traccarId, $name, $uniqueId, $phone, $model);
        } else {
            // Si no existe, creamos
            return $this->createDevice($name, $uniqueId, $phone, $model);
        }
    }

    public function createDevice($name, $uniqueId, $phone, $model)
    {
        $response = $this->client()->post("{$this->baseUrl}/devices", [
            'name' => $name,
            'uniqueId' => $uniqueId,
            'phone' => $phone,
            'model' => $model,
            // 'disabled' => false,
        ]);

        return $response->json();
    }

    public function updateDevice($id, $name, $uniqueId, $phone, $model)
    {
        $response = $this->client()->put("{$this->baseUrl}/devices/{$id}", [
            'id' => $id,
            'name' => $name,
            'uniqueId' => $uniqueId,
            'phone' => $phone,
            'model' => $model,
        ]);

        return $response->json();
    }

    public function deleteDevice($traccarId)
    {
        return $this->client()->delete("{$this->baseUrl}/devices/{$traccarId}");
    }

    /**
     * 2. Obtener Posiciones en Tiempo Real
     */
    public function getPositions()
    {
        // Retorna las últimas posiciones conocidas de TODOS los dispositivos
        return $this->client()->get("{$this->baseUrl}/positions");
    }

    /**
     * 3. Enviar Comandos (Apagado de Motor, etc)
     */
    public function sendCommand($deviceId, $type, $attributes = [])
    {
        // Tipos comunes: 'engineStop', 'engineResume', 'custom'
        $payload = [
            'deviceId' => $deviceId,
            'type' => $type,
            'attributes' => $attributes
        ];

        $response = $this->client()->post("{$this->baseUrl}/commands/send", $payload);
        
        if ($response->failed()) {
            Log::error('Error enviando comando Traccar', ['payload' => $payload, 'error' => $response->body()]);
            return false;
        }

        return true;
    }

    /**
     * 4. Crear Geocerca (NUEVO: Requerido por GeofenceController)
     * @param string $name Nombre de la zona
     * @param string $area Cadena WKT (POLYGON((...)))
     */
    public function createGeofence($name, $area, $description = '')
    {
        $response = $this->client()->post("{$this->baseUrl}/geofences", [
            'name' => $name,
            'area' => $area,
            'description' => $description,
        ]);

        if ($response->successful()) {
            return $response->json()['id']; // Retorna el ID creado en Traccar
        }

        // Loguear error para debug
        Log::error('Error creando geocerca en Traccar: ' . $response->body());
        
        // No lanzamos excepción para que no rompa la app, pero retornamos null
        return null;
    }
}