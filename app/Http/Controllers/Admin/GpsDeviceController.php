<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsDevice;
use App\Models\Customer;
use App\Models\TraccarDevice; // Modelo DB (Lectura rápida)
use App\Services\TraccarApiService; // Servicio API (Escritura/Comandos)
use Illuminate\Http\Request;

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
                  ->orWhere('unique_id', 'LIKE', "%$s%")
                  ->orWhere('plate_number', 'LIKE', "%$s%");
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(20);

        // Obtener estado online desde DB Traccar (más eficiente que API para listas grandes)
        $imeis = $devices->pluck('unique_id')->toArray();
        $traccarData = [];
        try {
            $traccarData = TraccarDevice::whereIn('uniqueid', $imeis)->get()->keyBy('uniqueid');
        } catch (\Exception $e) { 
            // Falla silenciosa si la DB Traccar no está disponible
        }

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
            'unique_id' => 'required|string|unique:gps_devices,unique_id', // IMEI
            'device_model' => 'required|string',
            'phone_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'vehicle_type' => 'required',
        ]);

        // 1. Crear en SeguCore (DB Local)
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

    /**
     * 4. DETALLE Y MAPA (Show)
     */
    public function show($id)
    {
        $device = GpsDevice::with('customer')->findOrFail($id);
        
        // Intentar obtener datos en vivo de la DB Traccar
        $liveData = null;
        try {
            $liveData = TraccarDevice::where('uniqueid', $device->unique_id)->first();
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
        ]);

        // 1. Actualizar Local
        $device->update($validated);

        // 2. Actualizar en Traccar API (Solo nombre, telefono y modelo)
        try {
            $this->traccarApi->syncDevice(
                $device->name,
                $device->unique_id, // El IMEI no suele cambiar, es la llave
                $device->phone_number,
                $device->device_model
            );
        } catch (\Exception $e) {
            // Log error
        }

        return back()->with('success', 'Datos del dispositivo actualizados.');
    }

    /**
     * 7. ELIMINAR (Destroy)
     */
    public function destroy($id)
    {
        $device = GpsDevice::findOrFail($id);
        
        // 1. Buscar ID interno de Traccar usando el modelo de solo lectura
        $traccarDevice = TraccarDevice::where('uniqueid', $device->unique_id)->first();

        // 2. Eliminar de Traccar API si existe
        if ($traccarDevice) {
            try {
                $this->traccarApi->deleteDevice($traccarDevice->id);
            } catch (\Exception $e) {
                // Continuar aunque falle Traccar
            }
        }

        // 3. Eliminar local
        $device->delete();

        return redirect()->route('admin.gps.devices.index')->with('success', 'Dispositivo eliminado.');
    }
    
    /**
     * 8. ENVIAR COMANDOS (AJAX)
     */
    public function sendCommand(Request $request, $id)
    {
        $device = GpsDevice::findOrFail($id);
        $traccarDevice = TraccarDevice::where('uniqueid', $device->unique_id)->firstOrFail();
        
        $type = $request->input('type'); // ej: engineStop, engineResume
        
        $success = $this->traccarApi->sendCommand($traccarDevice->id, $type);
        
        return response()->json(['success' => $success]);
    }
}