<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Geofence; // <--- Importante: Importar modelo Geofence
use App\Models\TraccarDevice;
use App\Models\TraccarPosition;
use App\Services\TraccarApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

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
        // Cargamos también la relación 'driver'
        $query = GpsDevice::with(['customer', 'driver']);

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
        
        // Cargar listas para los select
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $geofences = Geofence::orderBy('name')->get(); // <--- Cargar geocercas
        
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
                'required',
                'string',
                Rule::unique('gps_devices', 'imei')->whereNull('deleted_at')
            ],
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'vehicle_type' => 'required',
            'geofences'    => 'nullable|array', // <--- Validar array de geocercas
            'geofences.*'  => 'exists:geofences,id'
        ]);

        // Crear en SeguCore
        $device = GpsDevice::create($validated + ['subscription_status' => 'active']);

        // Asignar Geocercas (Tabla Pivote)
        if ($request->has('geofences')) {
            $device->geofences()->sync($request->geofences);
        }

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
            ->with('success', 'Dispositivo GPS registrado y asignado correctamente.');
    }

    /**
     * 4. DETALLE Y MAPA EN VIVO (Show)
     */
    public function show($id)
    {
        // Cargar geocercas para mostrarlas en el detalle si se desea
        $device = GpsDevice::with(['customer', 'driver', 'geofences'])->findOrFail($id);
        
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
        $device = GpsDevice::with('geofences')->findOrFail($id); // Cargar geocercas asignadas
        
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
            'geofences' => 'nullable|array', // <--- Validar array
            'geofences.*' => 'exists:geofences,id'
        ]);

        $device->update($validated);

        // Sincronizar Geocercas (Tabla Pivote)
        // El método sync() elimina las que no estén en el array y agrega las nuevas
        $device->geofences()->sync($request->input('geofences', []));

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
        
        // ZONA HORARIA DE TU PAÍS (Ajusta si es necesario)
        $tz = 'America/Caracas'; 

        // 1. Procesar fechas: Convertir Entrada (Local) -> Query (UTC)
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
    }

    /**
     * 12. EXPORTAR HISTORIAL A PDF
     */
    public function exportHistoryPdf(Request $request, $id)
    {
        $device = GpsDevice::with('customer', 'driver')->findOrFail($id);
        
        // Zona horaria para procesar los inputs correctamente
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

        // Generar PDF
        $pdf = Pdf::loadView('admin.gps.devices.pdf_history', compact('device', 'positions', 'from', 'to'));
        
        return $pdf->download("Historial_{$device->name}_{$from->format('Ymd')}.pdf");
    }
}