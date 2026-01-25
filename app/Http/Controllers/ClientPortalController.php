<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;
use App\Models\DeviceAlert;
use App\Models\TraccarPosition;
use App\Services\TraccarApiService; // Importamos el servicio real
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    protected $traccarService;

    // Inyectamos el servicio en el constructor si lo prefieres, 
    // o lo instanciamos directamente en el método.
    public function __construct(TraccarApiService $traccarService)
    {
        $this->traccarService = $traccarService;
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user->customer_id) {
            return view('client.map', ['user' => $user, 'error' => 'Sin cliente asignado.']);
        }
        return view('client.map', ['user' => $user]);
    }

    public function getAssets()
    {
        $user = Auth::user();
        if (!$user->customer_id) return response()->json(['assets' => []]);

        $assets = collect();

        // 1. ALARMAS
        $alarms = AlarmAccount::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->get();

        foreach ($alarms as $alarm) {
            if ($alarm->latitude && $alarm->longitude) {
                $assets->push([
                    'type' => 'alarm',
                    'id' => $alarm->id,
                    'lat' => (float)$alarm->latitude,
                    'lng' => (float)$alarm->longitude,
                    'status' => $alarm->monitoring_status ?? 'normal',
                    'name' => $alarm->name ?? $alarm->account_number,
                    'address' => $alarm->address,
                    'last_update' => $alarm->updated_at->diffForHumans(),
                ]);
            }
        }

        // 2. GPS
        $devices = GpsDevice::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->with('driver')
            ->get();

        foreach ($devices as $device) {
            $assets->push([
                'type' => 'gps',
                'id' => $device->id,
                'lat' => (float)$device->last_latitude,
                'lng' => (float)$device->last_longitude,
                'status' => $device->status ?? 'offline',
                'name' => $device->name ?? 'Móvil ' . $device->id,
                'speed' => round($device->speed, 0),
                'course' => $device->course ?? 0,
                'driver' => optional($device->driver)->first_name ?? 'Sin Asignar',
                'last_update' => $device->last_connection ? Carbon::parse($device->last_connection)->format('H:i d/m') : 'S/D',
            ]);
        }

        return response()->json(['assets' => $assets]);
    }

    public function getHistory(Request $request, $id)
    {
        $user = Auth::user();
        
        $device = GpsDevice::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$device) return response()->json(['error' => 'Dispositivo no encontrado'], 404);

        // Validación: El dispositivo debe estar vinculado a Traccar para tener historial
        if (!$device->traccar_device_id) {
            return response()->json(['error' => 'Este dispositivo no está sincronizado con el servidor de rastreo.'], 400);
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        if ($start->diffInDays($end) > 3) {
            return response()->json(['error' => 'El rango máximo de consulta es de 3 días.'], 400);
        }

        // Consulta optimizada seleccionando solo columnas necesarias
        $positions = TraccarPosition::where('device_id', $device->traccar_device_id)
            ->whereBetween('device_time', [$start, $end])
            ->orderBy('device_time', 'asc')
            ->select(['latitude', 'longitude', 'speed', 'device_time', 'course'])
            ->get(); //

        return response()->json(['positions' => $positions]);
    }

    public function getLatestAlerts()
    {
        $user = Auth::user();
        if (!$user->customer_id) return response()->json([]);

        $deviceIds = GpsDevice::where('customer_id', $user->customer_id)->pluck('id');

        $alerts = DeviceAlert::whereIn('gps_device_id', $deviceIds)
            ->where('created_at', '>=', now()->subHours(48))
            ->with('device:id,name')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'device' => $alert->device->name,
                    'message' => $alert->message,
                    'time' => $alert->created_at->diffForHumans(),
                    'type' => $alert->type
                ];
            });

        return response()->json($alerts);
    }

    /**
     * ENVÍO DE COMANDOS REAL (CONECTADO)
     */
    public function sendCommand(Request $request, $id)
    {
        $user = Auth::user();

        $device = GpsDevice::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->firstOrFail();

        $request->validate([
            'type' => 'required|in:engineStop,engineResume'
        ]);

        if (!$device->traccar_device_id) {
            return response()->json(['success' => false, 'message' => 'Dispositivo no vinculado a Traccar.'], 400);
        }

        try {
            // Usamos el servicio inyectado para enviar el comando real a la API
            $success = $this->traccarService->sendCommand(
                $device->traccar_device_id, 
                $request->type
            ); //

            if ($success) {
                return response()->json(['success' => true, 'message' => 'Comando enviado al satélite.']);
            } else {
                return response()->json(['success' => false, 'message' => 'El servidor rechazó el comando.'], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }

    // --- Modales ---

    public function modalGps($id)
    {
        $user = Auth::user();
        $device = GpsDevice::with('driver')
            ->where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$device) return '<div class="p-6 text-center text-red-500">Dispositivo no disponible</div>';

        return view('client.modals.gps', compact('device'));
    }

    public function modalAlarm($id)
    {
        $user = Auth::user();
        $account = AlarmAccount::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$account) return '<div class="p-6 text-center text-red-500">Cuenta de alarma no disponible</div>';

        return view('client.modals.alarm', compact('account'));
    }

    public function modalBilling()
    {
        $user = Auth::user();
        $invoices = Invoice::where('customer_id', $user->customer_id)
            ->latest()
            ->take(5)
            ->get();

        return view('client.modals.billing', compact('invoices'));
    }
}