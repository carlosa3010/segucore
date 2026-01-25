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

        $imeis = $devices->pluck('imei')->toArray();
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)->get()->keyBy('uniqueid');
        } catch (\Exception $e) { }

        return view('admin.gps.devices.index', compact('devices', 'traccarData'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $geofences = Geofence::orderBy('name')->get(); 
        
        return view('admin.gps.devices.create', compact('customers', 'drivers', 'geofences'));
    }

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
            'model' => 'required|string', // Antes device_model, corregido a model según migration
            'sim_card_number' => 'nullable|string', // Antes phone_number, corregido según migration
            'plate_number' => 'nullable|string',
            'geofences'    => 'nullable|array',
            'geofences.*'  => 'exists:geofences,id'
        ]);

        // CORRECCIÓN: Eliminado 'subscription_status' y mapping de campos correctos
        $device = GpsDevice::create([
            'customer_id' => $request->customer_id,
            'driver_id' => $request->driver_id,
            'name' => $request->name,
            'imei' => $request->imei,
            'model' => $request->model,
            'sim_card_number' => $request->sim_card_number,
            'plate_number' => $request->plate_number,
            'is_active' => true, // Usamos is_active en lugar de subscription_status
            'settings' => ['vehicle_type' => $request->input('vehicle_type', 'car')]
        ]);

        if ($request->has('geofences')) {
            $device->geofences()->sync($request->geofences);
        }

        // Sincronizar con Traccar
        try {
            $traccarResponse = $this->traccarApi->syncDevice(
                $device->name,
                $device->imei,
                $device->sim_card_number,
                $device->model
            );

            if ($request->has('geofences') && isset($traccarResponse['id'])) {
                $traccarDeviceId = $traccarResponse['id'];
                $selectedGeofences = Geofence::whereIn('id', $request->geofences)
                    ->whereNotNull('traccar_id')->get();

                foreach ($selectedGeofences as $geo) {
                    $this->traccarApi->linkGeofenceToDevice($traccarDeviceId, $geo->traccar_id);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error sync Traccar en store: " . $e->getMessage());
            return redirect()->route('admin.gps.devices.index')
                ->with('warning', 'Guardado localmente, pero error en Traccar API.');
        }

        return redirect()->route('admin.gps.devices.index')
            ->with('success', 'Dispositivo registrado correctamente.');
    }

    public function show($id)
    {
        $device = GpsDevice::with(['customer', 'driver', 'geofences'])->findOrFail($id);
        
        $liveData = null;
        try {
            $liveData = TraccarDevice::where('uniqueid', $device->imei)->with('position')->first();
        } catch (\Exception $e) { }

        return view('admin.gps.devices.show', compact('device', 'liveData'));
    }

    public function edit($id)
    {
        $device = GpsDevice::with('geofences')->findOrFail($id);
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $geofences = Geofence::orderBy('name')->get();

        return view('admin.gps.devices.edit', compact('device', 'customers', 'drivers', 'geofences'));
    }

    public function update(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'driver_id' => 'nullable|exists:drivers,id',
            'model' => 'required|string',
            'sim_card_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'is_active' => 'required|boolean', // Validamos boolean
            'speed_limit' => 'nullable|integer|min:0',
            'geofences' => 'nullable|array',
            'geofences.*' => 'exists:geofences,id'
        ]);

        $device->update($validated);

        $syncResult = $device->geofences()->sync($request->input('geofences', []));

        try {
            $traccarResponse = $this->traccarApi->syncDevice(
                $device->name,
                $device->imei,
                $device->sim_card_number,
                $device->model
            );

            if (isset($traccarResponse['id'])) {
                $traccarDeviceId = $traccarResponse['id'];

                if (!empty($syncResult['attached'])) {
                    $newGeofences = Geofence::whereIn('id', $syncResult['attached'])->whereNotNull('traccar_id')->get();
                    foreach ($newGeofences as $geo) {
                        $this->traccarApi->linkGeofenceToDevice($traccarDeviceId, $geo->traccar_id);
                    }
                }

                if (!empty($syncResult['detached'])) {
                    $removedGeofences = Geofence::whereIn('id', $syncResult['detached'])->whereNotNull('traccar_id')->get();
                    foreach ($removedGeofences as $geo) {
                        $this->traccarApi->unlinkGeofenceFromDevice($traccarDeviceId, $geo->traccar_id);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error sync Traccar en update: " . $e->getMessage());
        }

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    public function destroy($id)
    {
        $device = GpsDevice::findOrFail($id);
        try {
            $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
            if ($traccarDevice) {
                $this->traccarApi->deleteDevice($traccarDevice->id);
            }
        } catch (\Exception $e) { 
            Log::warning("Error eliminando en Traccar: " . $e->getMessage());
        }
        $device->delete();
        return redirect()->route('admin.gps.devices.index')->with('success', 'Dispositivo eliminado.');
    }
    
    public function sendCommand(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        try {
            $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->firstOrFail();
            $success = $this->traccarApi->sendCommand($traccarDevice->id, $request->input('type'));
            return response()->json(['success' => $success]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

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

    public function history($id)
    {
        $device = GpsDevice::findOrFail($id);
        return view('admin.gps.devices.history', compact('device'));
    }

    public function getHistoryData(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        $tz = 'America/Caracas'; 

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

            $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
                ->whereBetween('fixtime', [$from, $to])
                ->orderBy('fixtime', 'asc') 
                ->get()
                ->map(function ($p) use ($tz) {
                    return [
                        'lat' => $p->latitude,
                        'lng' => $p->longitude,
                        'speed' => round($p->speed * 1.852), 
                        'time' => \Carbon\Carbon::parse($p->fixtime)->setTimezone($tz)->format('d/m H:i'),
                        'course' => $p->course
                    ];
                });

            return response()->json($positions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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