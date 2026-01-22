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

    private function client()
    {
        return Http::withBasicAuth($this->user, $this->password)
            ->acceptJson()
            ->timeout(10);
    }

    // ... (Mantenemos los métodos de Dispositivos syncDevice, createDevice, etc...)

    public function sendCommand($deviceId, $type, $attributes = [])
    {
        // ... (Tu código existente) ...
        $payload = ['deviceId' => $deviceId, 'type' => $type, 'attributes' => $attributes];
        $response = $this->client()->post("{$this->baseUrl}/commands/send", $payload);
        if ($response->failed()) {
            Log::error('Error enviando comando Traccar', ['payload' => $payload, 'error' => $response->body()]);
            return false;
        }
        return true;
    }

    // --- NUEVOS MÉTODOS PARA GEOCERCAS ---

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
        return null;
    }

    public function deleteGeofence($traccarId)
    {
        // Endpoint: DELETE /api/geofences/{id}
        return $this->client()->delete("{$this->baseUrl}/geofences/{$traccarId}");
    }
}