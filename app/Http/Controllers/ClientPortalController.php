<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;
use App\Models\DeviceAlert;
use App\Models\TraccarPosition;
use App\Models\TraccarDevice; // <--- IMPORTANTE: Necesario para buscar por IMEI
use App\Services\TraccarApiService;
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    protected $traccarService;

    public function __construct(TraccarApiService $traccarService)
    {
        $this->traccarService = $traccarService;
    }

    /**
     * Vista Principal (Mapa)
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->customer_id) {
            return view('client.map', ['user' => $user, 'error' => 'Sin cliente asignado.']);
        }
        return view('client.map', ['user' => $user]);
    }

    /**
     * API: Obtener todos los activos (Alarmas + GPS)
     */
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

    /**
     * API: Historial de Recorrido
     */
    public function getHistory(Request $request, $id)
    {
        $user = Auth::user();
        
        // 1. Validar propiedad del dispositivo local
        $device = GpsDevice::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$device) return response()->json(['error' => 'Dispositivo no encontrado'], 404);

        // 2. Buscar el dispositivo en Traccar usando el IMEI (Igual que en Admin)
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();

        if (!$traccarDevice) {
            return response()->json(['error' => 'Este dispositivo no está sincronizado con el servidor de rastreo.'], 400);
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        // Limitación de seguridad (3 días)
        if ($start->diffInDays($end) > 3) {
            return response()->json(['error' => 'El rango máximo de consulta es de 3 días.'], 400);
        }

        // 3. Consultar posiciones usando el ID de Traccar recuperado
        // Nota: Asegúrate que la columna foránea en TraccarPosition sea 'deviceid' (según tu Admin controller)
        $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
            ->whereBetween('fixtime', [$start->setTimezone('UTC'), $end->setTimezone('UTC')]) // Traccar guarda en UTC
            ->orderBy('fixtime', 'asc')
            ->select(['latitude', 'longitude', 'speed', 'fixtime as device_time', 'course'])
            ->get();

        return response()->json(['positions' => $positions]);
    }

    /**
     * API: Enviar Comandos (Corte/Restaurar)
     */
    public function sendCommand(Request $request, $id)
    {
        $user = Auth::user();

        // 1. Validar propiedad local
        $device = GpsDevice::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->firstOrFail();

        $request->validate([
            'type' => 'required|in:engineStop,engineResume'
        ]);

        // 2. Buscar dispositivo en Traccar por IMEI (Corrección clave)
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();

        if (!$traccarDevice) {
            return response()->json(['success' => false, 'message' => 'Error: Dispositivo no sincronizado (IMEI no encontrado en Traccar).'], 400);
        }

        try {
            // 3. Enviar comando usando el ID real de Traccar
            $success = $this->traccarService->sendCommand(
                $traccarDevice->id, // ID de la tabla tc_devices
                $request->type
            );

            if ($success) {
                return response()->json(['success' => true, 'message' => 'Comando enviado al satélite.']);
            } else {
                return response()->json(['success' => false, 'message' => 'El servidor rechazó el comando.'], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Últimas Alertas
     */
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

    // --- MODALES (Vistas HTML) ---

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