<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\Customer;
use App\Models\TraccarDevice; // Modelo DB (Solo lectura)
use App\Services\TraccarApiService; // Nuevo Servicio API
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
        // ... (Tu código de búsqueda existente) ...
        $devices = $query->orderBy('created_at', 'desc')->paginate(20);

        // Obtener estado online desde DB (es más rápido que llamar a la API para esto)
        $imeis = $devices->pluck('unique_id')->toArray();
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)->get()->keyBy('uniqueid');
        } catch (\Exception $e) { }

        return view('admin.gps.devices.index', compact('devices', 'traccarData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:100',
            'unique_id' => 'required|string|unique:gps_devices,unique_id', // IMEI
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'vehicle_type' => 'required',
        ]);

        // 1. Crear en SeguCore
        $device = GpsDevice::create($validated + ['subscription_status' => 'active']);

        // 2. Sincronizar con API Traccar
        try {
            $this->traccarApi->syncDevice(
                $device->name,
                $device->unique_id,
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

    public function destroy($id)
    {
        $device = GpsDevice::findOrFail($id);
        
        // 1. Obtener ID de Traccar (buscando por IMEI en la DB conectada)
        $traccarDevice = TraccarDevice::where('uniqueid', $device->unique_id)->first();

        // 2. Eliminar de Traccar API
        if ($traccarDevice) {
            try {
                $this->traccarApi->deleteDevice($traccarDevice->id);
            } catch (\Exception $e) {
                // Log error pero continuar
            }
        }

        // 3. Eliminar local
        $device->delete();

        return redirect()->route('admin.gps.devices.index')->with('success', 'Dispositivo eliminado.');
    }
    
    // Método para enviar comandos (AJAX)
    public function sendCommand(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        $traccarDevice = TraccarDevice::where('uniqueid', $device->unique_id)->firstOrFail();
        
        $type = $request->input('type'); // engineStop, engineResume
        
        $success = $this->traccarApi->sendCommand($traccarDevice->id, $type);
        
        return response()->json(['success' => $success]);
    }
}