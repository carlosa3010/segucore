<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\TraccarDevice;
use App\Models\Customer; // Importar Modelo
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query base con filtro de cliente
        $query = GpsDevice::with('customer')->orderBy('name');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $devices = $query->get();
        $customers = Customer::orderBy('first_name')->get(); // Para el select

        // 2. Obtener IDs para Traccar
        $imeis = $devices->pluck('imei')->toArray();
        
        // 3. Datos Traccar
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)
                ->with('position')
                ->get()
                ->keyBy('uniqueid');
        } catch (\Exception $e) { }

        // 4. Estadísticas (Calculadas sobre los dispositivos filtrados)
        $stats = [
            'total' => $devices->count(),
            'online' => 0, 'offline' => 0, 'moving' => 0, 'stopped' => 0,
        ];

        foreach ($devices as $dev) {
            $tData = $traccarData[$dev->imei] ?? null;
            if ($tData && $tData->status == 'online') {
                $stats['online']++;
                if ($tData->position && $tData->position->speed > 1) $stats['moving']++;
                else $stats['stopped']++;
            } else {
                $stats['offline']++;
            }
        }

        return view('admin.gps.fleet.index', compact('devices', 'stats', 'customers'));
    }

    public function positions(Request $request)
    {
        // Aplicamos el mismo filtro al AJAX para no mostrar autos que no corresponden
        $query = GpsDevice::query();
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        $devices = $query->get();
        $imeis = $devices->pluck('imei')->toArray();

        try {
            $traccarDevices = TraccarDevice::whereIn('uniqueid', $imeis)->with('position')->get();

            $features = $traccarDevices->map(function($td) use ($devices) {
                $localDev = $devices->firstWhere('imei', $td->uniqueid);
                if (!$td->position || !$localDev) return null;

                return [
                    'id' => $localDev->id,
                    'name' => $localDev->name,
                    'plate' => $localDev->plate_number, // Agregado placa
                    'imei' => $td->uniqueid,
                    'lat' => $td->position->latitude,
                    'lng' => $td->position->longitude,
                    'speed' => round($td->position->speed * 1.852),
                    'status' => $td->status,
                    'ignition' => $td->position->attributes['ignition'] ?? false, // Ignición
                    'course' => $td->position->course,
                    'last_update' => \Carbon\Carbon::parse($td->lastupdate)->diffForHumans(null, true, true)
                ];
            })->filter();

            return response()->json($features->values());

        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}