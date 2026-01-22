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
        return $this->client()->get("{$this->baseUrl}/positions");
    }

    /**
     * 3. Enviar Comandos
     */
    public function sendCommand($deviceId, $type, $attributes = [])
    {
        $payload = [
            'deviceId' => $deviceId,
            'type' => $type,
            // CORRECCIÓN: (object) fuerza a PHP a enviar "{}" en lugar de "[]"
            'attributes' => empty($attributes) ? (object)[] : $attributes
        ];

        // Usar asJson() para asegurar la cabecera Content-Type: application/json
        $response = $this->client()->asJson()->post("{$this->baseUrl}/commands/send", $payload);
        
        if ($response->failed()) {
            // Es útil ver el error real en el log (storage/logs/laravel.log)
            Log::error('Error enviando comando Traccar', [
                'payload' => $payload, 
                'status' => $response->status(),
                'error' => $response->body()
            ]);
            return false;
        }

        return true;
    }

    /**
     * 4. Gestión de Geocercas (CRUD)
     */
    
    // CREAR
    public function createGeofence($name, $area, $description = '')
    {
        $response = $this->client()->post("{$this->baseUrl}/geofences", [
            'name' => $name,
            'area' => $area,
            'description' => $description,
        ]);

        if ($response->successful()) {
            return $response->json()['id'];
        }

        Log::error('Error creando geocerca en Traccar: ' . $response->body());
        return null;
    }

    // EDITAR (Nuevo)
    public function updateGeofence($id, $name, $area, $description = '')
    {
        $response = $this->client()->put("{$this->baseUrl}/geofences/{$id}", [
            'id' => $id,
            'name' => $name,
            'area' => $area,
            'description' => $description,
        ]);

        if ($response->successful()) {
            return true;
        }

        Log::error("Error actualizando geocerca {$id} en Traccar: " . $response->body());
        return false;
    }

    // ELIMINAR (Nuevo)
    public function deleteGeofence($traccarId)
    {
        $response = $this->client()->delete("{$this->baseUrl}/geofences/{$traccarId}");
        return $response->successful();
    }
}