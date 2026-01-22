<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patrol;
use App\Models\Guard;
use App\Services\TraccarApiService;
use Illuminate\Http\Request;

class SecurityMapController extends Controller
{
    protected $traccar;

    public function __construct(TraccarApiService $traccar)
    {
        $this->traccar = $traccar;
    }

    public function index()
    {
        return view('admin.patrols.map');
    }

    // API Endpoint para alimentar el mapa (AJAX)
    public function positions()
    {
        // 1. OBTENER POSICIONES DE VEHÍCULOS (Desde Traccar)
        $traccarPositions = collect([]);
        try {
            $response = $this->traccar->getPositions();
            if ($response->successful()) {
                $traccarPositions = collect($response->json())->keyBy('deviceId');
            }
        } catch (\Exception $e) { }

        // 2. PROCESAR PATRULLAS (Cruzar BD local con Traccar)
        $patrols = Patrol::with('gpsDevice')->where('is_active', true)->get()->map(function($patrol) use ($traccarPositions) {
            $lat = null; $lng = null; $speed = 0; $lastUpdate = null;

            // Si tiene GPS físico asignado
            if ($patrol->gpsDevice && $patrol->gpsDevice->traccar_device_id) {
                $pos = $traccarPositions->get($patrol->gpsDevice->traccar_device_id);
                if ($pos) {
                    $lat = $pos['latitude'];
                    $lng = $pos['longitude'];
                    $speed = $pos['speed'] * 1.852; // Nudos a Km/h
                    $lastUpdate = $pos['deviceTime'];
                }
            }

            return [
                'id' => 'patrol_' . $patrol->id,
                'type' => 'patrol',
                'subtype' => $patrol->vehicle_type, // car, motorcycle
                'name' => $patrol->name,
                'plate' => $patrol->plate_number,
                'lat' => $lat,
                'lng' => $lng,
                'speed' => round($speed),
                'status' => 'online', // Podrías calcular esto basado en lastUpdate
                'icon' => $this->getIconForType($patrol->vehicle_type)
            ];
        })->filter(fn($p) => $p['lat'] != null); // Solo mostrar si tienen ubicación

        // 3. PROCESAR GUARDIAS (Desde BD Local - App Tracking)
        $guards = Guard::where('on_duty', true)
            ->whereNotNull('last_lat')
            ->get() // Filtrar por última actualización reciente (ej. 1 hora)
            ->map(function($guard) {
                return [
                    'id' => 'guard_' . $guard->id,
                    'type' => 'guard',
                    'name' => $guard->full_name,
                    'lat' => (float)$guard->last_lat,
                    'lng' => (float)$guard->last_lng,
                    'battery' => $guard->battery_level,
                    'last_seen' => $guard->last_seen_at,
                    'icon' => '👮'
                ];
            });

        // 4. UNIFICAR Y RETORNAR
        return response()->json($patrols->merge($guards));
    }

    private function getIconForType($type)
    {
        return match($type) {
            'motorcycle' => '🏍️',
            'foot' => '🚶',
            'bicycle' => '🚲',
            'dog' => '🐕', // Unitree a futuro
            default => '🚓',
        };
    }
}