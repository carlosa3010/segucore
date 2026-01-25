<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Geofence;
use App\Models\TraccarDevice;
use App\Models\TraccarPosition;
use App\Services\TraccarApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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
        $query = GpsDevice::with(['customer', 'driver']);

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'LIKE', "%$s%")
                  ->orWhere('imei', 'LIKE', "%$s%")
                  ->orWhere('plate_number', 'LIKE', "%$s%");
            });
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
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $geofences = Geofence::orderBy('name')->get(); 
        
        return view('admin.gps.devices.create', compact('customers', 'drivers', 'geofences'));
    }

    /**
     * 3. GUARDAR Y SINCRONIZAR (Store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'driver_id'   => 'nullable|exists:drivers,id',
            'name' => 'required|string|max:100',
            'imei' => [
                'required', 'string',
                Rule::unique('gps_devices', 'imei')->whereNull('deleted_at')
            ],
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'vehicle_type' => 'required',
            'geofences'    => 'nullable|array',
            'geofences.*'  => 'exists:geofences,id'
        ]);

        // 1. Crear localmente
        $device = GpsDevice::create($validated + ['subscription_status' => 'active']);

        // 2. Asignar Geocercas (Pivote local)
        if ($request->has('geofences')) {
            $device->geofences()->sync($request->geofences);
        }

        // 3. Sincronizar con Traccar API
        try {
            // Crear/Actualizar dispositivo en Traccar y obtener su respuesta (que incluye el ID)
            $traccarResponse = $this->traccarApi->syncDevice(
                $device->name,
                $device->imei,
                $device->phone_number,
                $device->device_model
            );

            // Si hay geocercas y tenemos respuesta exitosa de Traccar
            if ($request->has('geofences') && isset($traccarResponse['id'])) {
                $traccarDeviceId = $traccarResponse['id'];
                
                // Buscar las geocercas seleccionadas para obtener sus traccar_ids
                $selectedGeofences = Geofence::whereIn('id', $request->geofences)
                    ->whereNotNull('traccar_id')
                    ->get();

                foreach ($selectedGeofences as $geo) {
                    $this->traccarApi->linkGeofenceToDevice($traccarDeviceId, $geo->traccar_id);
                }
            }

        } catch (\Exception $e) {
            Log::error("Error sync Traccar en store: " . $e->getMessage());
            return redirect()->route('admin.gps.devices.index')
                ->with('warning', 'Dispositivo guardado, pero hubo un error de conexión con el servidor GPS.');
        }

        return redirect()->route('admin.gps.devices.index')
            ->with('success', 'Dispositivo GPS registrado y configurado correctamente.');
    }

    /**
     * 4. DETALLE Y MAPA EN VIVO (Show)
     */
    public function show($id)
    {
        $device = GpsDevice::with(['customer', 'driver', 'geofences'])->findOrFail($id);
        
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
        $device = GpsDevice::with('geofences')->findOrFail($id);
        
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $geofences = Geofence::orderBy('name')->get();

        return view('admin.gps.devices.edit', compact('device', 'customers', 'drivers', 'geofences'));
    }

    /**
     * 6. ACTUALIZAR (Update)
     */
    public function update(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'driver_id' => 'nullable|exists:drivers,id',
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'subscription_status' => 'required|in:active,suspended',
            'speed_limit' => 'nullable|integer|min:0',
            'odometer' => 'nullable|numeric|min:0',
            'geofences' => 'nullable|array',
            'geofences.*' => 'exists:geofences,id'
        ]);

        $device->update($validated);

        // 1. Sincronizar tabla pivote local y capturar cambios (attached/detached)
        $syncResult = $device->geofences()->sync($request->input('geofences', []));

        // 2. Sincronizar con Traccar
        try {
            $traccarResponse = $this->traccarApi->syncDevice(
                $device->name,
                $device->imei,
                $device->phone_number,
                $device->device_model
            );

            if (isset($traccarResponse['id'])) {
                $traccarDeviceId = $traccarResponse['id'];

                // A. Vincular Nuevas (Attached)
                if (!empty($syncResult['attached'])) {
                    $newGeofences = Geofence::whereIn('id', $syncResult['attached'])->whereNotNull('traccar_id')->get();
                    foreach ($newGeofences as $geo) {
                        $this->traccarApi->linkGeofenceToDevice($traccarDeviceId, $geo->traccar_id);
                    }
                }

                // B. Desvincular Removidas (Detached)
                if (!empty($syncResult['detached'])) {
                    $removedGeofences = Geofence::whereIn('id', $syncResult['detached'])->whereNotNull('traccar_id')->get();
                    foreach ($removedGeofences as $geo) {
                        $this->traccarApi->unlinkGeofenceFromDevice($traccarDeviceId, $geo->traccar_id);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("Error sync Traccar en update: " . $e->getMessage());
            // No retornamos error para no interrumpir la experiencia de usuario, pero queda en log.
        }

        return back()->with('success', 'Datos del dispositivo actualizados y sincronizados.');
    }

    /**
     * 7. ELIMINAR (Destroy)
     */
    public function destroy($id)
    {
        $device = GpsDevice::findOrFail($id);
        
        // Intentar borrar de Traccar primero
        try {
            $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
            if ($traccarDevice) {
                $this->traccarApi->deleteDevice($traccarDevice->id);
            }
        } catch (\Exception $e) { 
            Log::warning("No se pudo eliminar dispositivo de Traccar: " . $e->getMessage());
        }

        $device->delete();

        return redirect()->route('admin.gps.devices.index')->with('success', 'Dispositivo eliminado correctamente.');
    }
    
    /**
     * 8. ENVIAR COMANDOS (AJAX)
     */
    public function sendCommand(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        try {
            $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->firstOrFail();
            $type = $request->input('type');
            
            $success = $this->traccarApi->sendCommand($traccarDevice->id, $type);
            return response()->json(['success' => $success]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
        
        // ZONA HORARIA VENEZUELA
        $tz = 'America/Caracas'; 

        // 1. Procesar fechas: Convertir Entrada (Local) -> Query (UTC)
        try {
            if ($request->filled('from') && $request->filled('to')) {
                $from = \Carbon\Carbon::parse($request->input('from'), $tz)->setTimezone('UTC');
                $to = \Carbon\Carbon::parse($request->input('to'), $tz)->setTimezone('UTC');
            } else {
                $from = now()->subHours(12)->setTimezone('UTC');
                $to = now()->setTimezone('UTC');
            }

            $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
            if (!$traccarDevice) return response()->json([]);

            // 2. Consulta a BD (Usando tiempos UTC)
            $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
                ->whereBetween('fixtime', [$from, $to])
                ->orderBy('fixtime', 'asc') 
                ->get()
                ->map(function ($p) use ($tz) {
                    return [
                        'lat' => $p->latitude,
                        'lng' => $p->longitude,
                        'speed' => round($p->speed * 1.852), 
                        // 3. Salida: Convertir DB (UTC) -> Visualización (Local)
                        'time' => \Carbon\Carbon::parse($p->fixtime)->setTimezone($tz)->format('d/m H:i'),
                        'course' => $p->course
                    ];
                });

            return response()->json($positions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 12. EXPORTAR HISTORIAL A PDF
     */
    public function exportHistoryPdf(Request $request, $id)
    {
        $device = GpsDevice::with('customer', 'driver')->findOrFail($id);
        $tz = 'America/Caracas';

        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->input('from'), $tz);
            $to = \Carbon\Carbon::parse($request->input('to'), $tz);
        } else {
            $from = now($tz)->subHours(12);
            $to = now($tz);
        }

        // Obtener datos (Consulta a Traccar en UTC)
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        $positions = collect([]);

        if ($traccarDevice) {
            $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
                ->whereBetween('fixtime', [
                    $from->copy()->setTimezone('UTC'), 
                    $to->copy()->setTimezone('UTC')
                ])
                ->orderBy('fixtime', 'asc')
                ->get();
        }

        $pdf = Pdf::loadView('admin.gps.devices.pdf_history', compact('device', 'positions', 'from', 'to'));
        
        return $pdf->download("Historial_{$device->name}_{$from->format('Ymd')}.pdf");
    }
}