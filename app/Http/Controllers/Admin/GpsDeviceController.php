<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\Customer;
use App\Models\TraccarDevice;
use App\Services\TraccarApiService;
use Illuminate\Http\Request;

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
                  ->orWhere('imei', 'LIKE', "%$s%") // <--- CAMBIO
                  ->orWhere('plate_number', 'LIKE', "%$s%");
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(20);

        // Usamos 'imei' para buscar en Traccar
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
            'imei' => 'required|string|unique:gps_devices,imei', // <--- CAMBIO
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'vehicle_type' => 'required',
        ]);

        $device = GpsDevice::create($validated + ['subscription_status' => 'active']);

        // Sincronizar con API (Pasamos el imei como uniqueId)
        try {
            $this->traccarApi->syncDevice(
                $device->name,
                $device->imei, // <--- CAMBIO
                $device->phone_number,
                $device->device_model
            );
        } catch (\Exception $e) {
            return redirect()->route('admin.gps.devices.index')
                ->with('warning', 'Guardado localmente, pero falló conexión API Traccar.');
        }

        return redirect()->route('admin.gps.devices.index')
            ->with('success', 'Dispositivo registrado correctamente.');
    }

    public function show($id)
    {
        $device = GpsDevice::with('customer')->findOrFail($id);
        $liveData = null;
        try {
            $liveData = TraccarDevice::where('uniqueid', $device->imei)->first(); // <--- CAMBIO
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
                $device->imei, // <--- CAMBIO
                $device->phone_number,
                $device->device_model
            );
        } catch (\Exception $e) { }

        return back()->with('success', 'Dispositivo actualizado.');
    }

    public function destroy($id)
    {
        $device = GpsDevice::findOrFail($id);
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first(); // <--- CAMBIO

        if ($traccarDevice) {
            try {
                $this->traccarApi->deleteDevice($traccarDevice->id);
            } catch (\Exception $e) { }
        }

        $device->delete();
        return redirect()->route('admin.gps.devices.index')->with('success', 'Eliminado.');
    }

    public function sendCommand(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->firstOrFail(); // <--- CAMBIO
        
        $type = $request->input('type');
        $success = $this->traccarApi->sendCommand($traccarDevice->id, $type);
        
        return response()->json(['success' => $success]);
    }
}