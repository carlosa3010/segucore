<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\Customer;
use App\Models\TraccarDevice;
use App\Models\TraccarPosition;
use App\Services\TraccarApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GpsDeviceController extends Controller
{
    protected $traccarApi;

    public function __construct(TraccarApiService $traccarApi)
    {
        $this->traccarApi = $traccarApi;
    }

    /**
     * 1. LISTADO (Index)
     */
    public function index(Request $request)
    {
        $query = GpsDevice::with('customer');

        if ($request->has('search')) {
            $s = $request->search;
            $query->where('name', 'LIKE', "%$s%")
                  ->orWhere('imei', 'LIKE', "%$s%")
                  ->orWhere('plate_number', 'LIKE', "%$s%");
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(20);

        // Obtener estado online desde DB Traccar
        $imeis = $devices->pluck('imei')->toArray();
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)->get()->keyBy('uniqueid');
        } catch (\Exception $e) { }

        return view('admin.gps.devices.index', compact('devices', 'traccarData'));
    }

    /**
     * 2. FORMULARIO DE CREACIÓN (Create)
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        return view('admin.gps.devices.create', compact('customers'));
    }

    /**
     * 3. GUARDAR Y SINCRONIZAR (Store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:100',
            'imei' => [
                'required',
                'string',
                Rule::unique('gps_devices', 'imei')->whereNull('deleted_at')
            ],
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'vehicle_type' => 'required',
        ]);

        // Crear en SeguCore
        $device = GpsDevice::create($validated + ['subscription_status' => 'active']);

        // Sincronizar con API Traccar
        try {
            $this->traccarApi->syncDevice(
                $device->name,
                $device->imei,
                $device->phone_number,
                $device->device_model
            );
        } catch (\Exception $e) {
            return redirect()->route('admin.gps.devices.index')
                ->with('warning', 'Dispositivo creado localmente, pero falló la conexión con Traccar API.');
        }

        return redirect()->route('admin.gps.devices.index')
            ->with('success', 'Dispositivo GPS registrado y sincronizado.');
    }

    /**
     * 4. DETALLE Y MAPA EN VIVO (Show)
     */
    public function show($id)
    {
        $device = GpsDevice::with('customer')->findOrFail($id);
        
        // Datos en vivo + Posición (lat/lon/velocidad)
        $liveData = null;
        try {
            $liveData = TraccarDevice::where('uniqueid', $device->imei)
                ->with('position')
                ->first();
        } catch (\Exception $e) { }

        return view('admin.gps.devices.show', compact('device', 'liveData'));
    }

    /**
     * 5. FORMULARIO DE EDICIÓN (Edit)
     */
    public function edit($id)
    {
        $device = GpsDevice::findOrFail($id);
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        return view('admin.gps.devices.edit', compact('device', 'customers'));
    }

    /**
     * 6. ACTUALIZAR (Update)
     */
    public function update(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'subscription_status' => 'required|in:active,suspended',
            'speed_limit' => 'nullable|integer|min:0', // <--- Validación nuevo campo
            'odometer' => 'nullable|numeric|min:0',    // <--- Validación nuevo campo
        ]);

        $device->update($validated);

        try {
            $this->traccarApi->syncDevice(
                $device->name,
                $device->imei,
                $device->phone_number,
                $device->device_model
            );
        } catch (\Exception $e) { }

        return back()->with('success', 'Datos del dispositivo actualizados.');
    }

    /**
     * 7. ELIMINAR (Destroy)
     */
    public function destroy($id)
    {
        $device = GpsDevice::findOrFail($id);
        
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if ($traccarDevice) {
            try {
                $this->traccarApi->deleteDevice($traccarDevice->id);
            } catch (\Exception $e) { }
        }

        $device->delete();

        return redirect()->route('admin.gps.devices.index')->with('success', 'Dispositivo eliminado.');
    }
    
    /**
     * 8. ENVIAR COMANDOS (AJAX)
     */
    public function sendCommand(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->firstOrFail();
        
        $type = $request->input('type');
        
        $success = $this->traccarApi->sendCommand($traccarDevice->id, $type);
        
        return response()->json(['success' => $success]);
    }

    /**
     * 9. OBTENER RUTA EN VIVO (Últimas 50 posiciones)
     */
    public function getRoute($id)
    {
        $device = GpsDevice::findOrFail($id);
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();

        if (!$traccarDevice) return response()->json([]);

        $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
            ->orderBy('fixtime', 'desc')
            ->take(50)
            ->get()
            ->map(function ($pos) {
                return [
                    'lat' => $pos->latitude,
                    'lng' => $pos->longitude,
                    'time' => \Carbon\Carbon::parse($pos->fixtime)->format('H:i:s'),
                    'speed' => round($pos->speed * 1.852, 1)
                ];
            });

        return response()->json($positions->values());
    }

    /**
     * 10. VISTA DE HISTORIAL (Playback)
     */
    public function history($id)
    {
        $device = GpsDevice::findOrFail($id);
        return view('admin.gps.devices.history', compact('device'));
    }

    /**
     * 11. API DE HISTORIAL (Datos por rango de fecha)
     */
    public function getHistoryData(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        
        $from = $request->input('from') ?: now()->subHours(12);
        $to = $request->input('to') ?: now();

        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if (!$traccarDevice) return response()->json([]);

        $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
            ->whereBetween('fixtime', [$from, $to])
            ->orderBy('fixtime', 'asc') // Orden cronológico para dibujar la ruta correctamente
            ->get()
            ->map(function ($p) {
                return [
                    'lat' => $p->latitude,
                    'lng' => $p->longitude,
                    'speed' => round($p->speed * 1.852), // Km/h
                    'time' => \Carbon\Carbon::parse($p->fixtime)->format('d/m H:i'),
                    'course' => $p->course
                ];
            });

        return response()->json($positions);
    }
}