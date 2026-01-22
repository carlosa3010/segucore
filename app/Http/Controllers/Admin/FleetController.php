<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\TraccarDevice;
use App\Models\Customer;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $query = GpsDevice::with('customer')->orderBy('name');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $devices = $query->get();
        $customers = Customer::orderBy('first_name')->get();

        $imeis = $devices->pluck('imei')->toArray();
        
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)
                ->with('position')
                ->get()
                ->keyBy('uniqueid');
        } catch (\Exception $e) { }

        $stats = [
            'total' => $devices->count(),
            'online' => 0, 'offline' => 0, 'moving' => 0, 'stopped' => 0,
        ];

        foreach ($devices as $dev) {
            $tData = $traccarData[$dev->imei] ?? null;
            if ($tData) {
                if ($tData->status == 'online') {
                    $stats['online']++;
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

    public function positions(Request $request)
    {
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
                $localDev = $devices->firstWhere('imei', $td->uniqueid);
                
                if (!$td->position || !$localDev) return null;

                // Obtener nivel de batería (Traccar suele enviarlo como batteryLevel o battery)
                $attrs = $td->position->attributes ?? [];
                $battery = $attrs['batteryLevel'] ?? $attrs['battery'] ?? null;

                return [
                    'id' => $localDev->id,
                    'name' => $localDev->name,
                    'plate' => $localDev->plate_number ?? '',
                    'type'  => $localDev->vehicle_type, // <--- IMPORTANTE: Tipo de dispositivo
                    'imei' => $td->uniqueid,
                    'lat' => $td->position->latitude,
                    'lng' => $td->position->longitude,
                    'speed' => round($td->position->speed * 1.852),
                    'status' => $td->status,
                    'ignition' => $attrs['ignition'] ?? false,
                    'battery' => $battery, // <--- IMPORTANTE: Batería
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