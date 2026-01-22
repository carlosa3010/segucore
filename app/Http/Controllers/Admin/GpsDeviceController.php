<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\Customer;
use App\Models\TraccarDevice;
use App\Services\TraccarApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <--- AGREGAR ESTO

class GpsDeviceController extends Controller
{
    protected $traccarApi;

    public function __construct(TraccarApiService $traccarApi)
    {
        $this->traccarApi = $traccarApi;
    }

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
        return view('admin.gps.devices.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:100',
            // CORRECCIÓN AQUÍ: Ignorar registros eliminados (SoftDeletes)
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

        // Crear dispositivo localmente
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
                ->with('warning', 'Dispositivo creado localmente, pero no se pudo conectar con la API de Traccar.');
        }

        return redirect()->route('admin.gps.devices.index')
            ->with('success', 'Dispositivo GPS registrado y sincronizado correctamente.');
    }

    public function show($id)
    {
        $device = GpsDevice::with('customer')->findOrFail($id);
        
        $liveData = null;
        try {
            $liveData = TraccarDevice::where('uniqueid', $device->imei)->first();
        } catch (\Exception $e) { }

        return view('admin.gps.devices.show', compact('device', 'liveData'));
    }

    public function edit($id)
    {
        $device = GpsDevice::findOrFail($id);
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        return view('admin.gps.devices.edit', compact('device', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'subscription_status' => 'required|in:active,suspended',
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

    public function destroy($id)
    {
        $device = GpsDevice::findOrFail($id);
        
        // Intentar borrar de Traccar API
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if ($traccarDevice) {
            try {
                $this->traccarApi->deleteDevice($traccarDevice->id);
            } catch (\Exception $e) { }
        }

        $device->delete();

        return redirect()->route('admin.gps.devices.index')->with('success', 'Dispositivo eliminado.');
    }
    
    public function sendCommand(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->firstOrFail();
        
        $type = $request->input('type');
        
        $success = $this->traccarApi->sendCommand($traccarDevice->id, $type);
        
        return response()->json(['success' => $success]);
    }
}