<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\TraccarDevice;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    /**
     * Vista Principal del Dashboard de Flotas
     */
    public function index(Request $request)
    {
        // 1. Obtener todos los dispositivos locales
        $devices = GpsDevice::with('customer')->orderBy('name')->get();

        // 2. Obtener IDs de Traccar
        $imeis = $devices->pluck('imei')->toArray();
        
        // 3. Obtener datos de última posición masiva desde Traccar
        // Usamos 'with' position para no hacer N consultas
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)
                ->with('position')
                ->get()
                ->keyBy('uniqueid');
        } catch (\Exception $e) { }

        // 4. Calcular Estadísticas Rápidas
        $stats = [
            'total' => $devices->count(),
            'online' => 0,
            'offline' => 0,
            'moving' => 0,
            'stopped' => 0,
        ];

        foreach ($devices as $dev) {
            $tData = $traccarData[$dev->imei] ?? null;
            if ($tData) {
                if ($tData->status == 'online') {
                    $stats['online']++;
                    // Si velocidad > 1 nudo (aprox 2km/h), consideramos movimiento
                    if ($tData->position && $tData->position->speed > 1) {
                        $stats['moving']++;
                    } else {
                        $stats['stopped']++;
                    }
                } else {
                    $stats['offline']++;
                }
            } else {
                $stats['offline']++;
            }
        }

        return view('admin.gps.fleet.index', compact('devices', 'stats'));
    }

    /**
     * AJAX: Devuelve posiciones JSON de toda la flota para actualizar el mapa
     */
    public function positions()
    {
        $devices = GpsDevice::all();
        $imeis = $devices->pluck('imei')->toArray();

        try {
            $traccarDevices = TraccarDevice::whereIn('uniqueid', $imeis)
                ->with('position')
                ->get();

            $features = $traccarDevices->map(function($td) use ($devices) {
                // Cruzar con datos locales para tener el nombre bonito
                $localDev = $devices->firstWhere('imei', $td->uniqueid);
                
                if (!$td->position) return null;

                return [
                    'id' => $localDev ? $localDev->id : $td->id,
                    'name' => $localDev ? $localDev->name : $td->name,
                    'imei' => $td->uniqueid,
                    'lat' => $td->position->latitude,
                    'lng' => $td->position->longitude,
                    'speed' => round($td->position->speed * 1.852), // Km/h
                    'status' => $td->status,
                    'course' => $td->position->course, // Dirección (grados)
                    'last_update' => \Carbon\Carbon::parse($td->lastupdate)->diffForHumans(null, true, true)
                ];
            })->filter(); // Eliminar nulos

            return response()->json($features->values());

        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}