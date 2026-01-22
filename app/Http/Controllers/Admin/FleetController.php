<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\TraccarDevice;
use App\Models\Customer;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    /**
     * Vista Principal del Dashboard de Flotas
     */
    public function index(Request $request)
    {
        // 1. Query base de dispositivos
        $query = GpsDevice::with('customer')->orderBy('name');

        // Aplicar Filtro por Cliente si existe en el request
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $devices = $query->get();
        
        // Obtener lista de clientes para el dropdown
        $customers = Customer::orderBy('first_name')->get();

        // 2. Obtener IDs de Traccar
        $imeis = $devices->pluck('imei')->toArray();
        
        // 3. Obtener datos de última posición masiva desde Traccar
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)
                ->with('position')
                ->get()
                ->keyBy('uniqueid');
        } catch (\Exception $e) { }

        // 4. Calcular Estadísticas Rápidas (Sobre los resultados filtrados)
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
                    // Si velocidad > 1 nudo (aprox 1.8 km/h), consideramos movimiento
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

        return view('admin.gps.fleet.index', compact('devices', 'stats', 'customers'));
    }

    /**
     * AJAX: Devuelve posiciones JSON de la flota (Filtrada o completa)
     */
    public function positions(Request $request)
    {
        // Aplicamos el mismo filtro al AJAX para que el mapa coincida con la lista
        $query = GpsDevice::query();
        
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        $devices = $query->get();
        $imeis = $devices->pluck('imei')->toArray();

        try {
            $traccarDevices = TraccarDevice::whereIn('uniqueid', $imeis)
                ->with('position')
                ->get();

            $features = $traccarDevices->map(function($td) use ($devices) {
                // Cruzar con datos locales para obtener nombre y placa
                $localDev = $devices->firstWhere('imei', $td->uniqueid);
                
                if (!$td->position || !$localDev) return null;

                return [
                    'id' => $localDev->id,
                    'name' => $localDev->name,
                    'plate' => $localDev->plate_number ?? '', // Dato extra para el popup
                    'imei' => $td->uniqueid,
                    'lat' => $td->position->latitude,
                    'lng' => $td->position->longitude,
                    'speed' => round($td->position->speed * 1.852), // Km/h
                    'status' => $td->status,
                    'ignition' => $td->position->attributes['ignition'] ?? false, // Dato extra ignición
                    'course' => $td->position->course,
                    'last_update' => \Carbon\Carbon::parse($td->lastupdate)->diffForHumans(null, true, true)
                ];
            })->filter(); // Eliminar nulos

            return response()->json($features->values());

        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}