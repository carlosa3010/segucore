<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;
use App\Models\DeviceAlert;
use App\Models\TraccarPosition; // Asegúrate de tener este modelo o ajustar la consulta
use Carbon\Carbon;

class ClientPortalController extends Controller
{
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
        
        // Validar propiedad del dispositivo
        $device = GpsDevice::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$device) return response()->json(['error' => 'Dispositivo no encontrado'], 404);

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        // Limitación de seguridad
        if ($start->diffInDays($end) > 3) {
            return response()->json(['error' => 'El rango máximo de consulta es de 3 días.'], 400);
        }

        // Obtener posiciones (Ajusta 'traccar_device_id' según tu DB)
        // Si no usas Traccar directo, aquí iría tu lógica de historial
        $positions = TraccarPosition::where('device_id', $device->traccar_device_id)
            ->whereBetween('device_time', [$start, $end])
            ->orderBy('device_time', 'asc')
            ->get(['latitude', 'longitude', 'speed', 'device_time', 'course']);

        return response()->json(['positions' => $positions]);
    }

    /**
     * API: Últimas Alertas
     */
    public function getLatestAlerts()
    {
        $user = Auth::user();
        if (!$user->customer_id) return response()->json([]);

        // Obtener IDs de mis dispositivos GPS
        $deviceIds = GpsDevice::where('customer_id', $user->customer_id)->pluck('id');

        $alerts = DeviceAlert::whereIn('gps_device_id', $deviceIds)
            ->where('created_at', '>=', now()->subHours(48)) // Últimas 48 horas
            ->with('device:id,name')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'device' => $alert->device->name,
                    'message' => $alert->message, // Ej: "Exceso de velocidad"
                    'time' => $alert->created_at->diffForHumans(),
                    'type' => $alert->type
                ];
            });

        return response()->json($alerts);
    }

    /**
     * API: Enviar Comandos (Corte/Restaurar)
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

        try {
            // AQUÍ LLAMARÍAS A TU SERVICIO TRACCAR O PLATAFORMA GPS
            // Ejemplo: TraccarApiService::sendCommand($device->traccar_device_id, $request->type);
            
            // Simulamos éxito para la demo
            return response()->json(['success' => true, 'message' => 'Comando enviado correctamente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de comunicación con el dispositivo.'], 500);
        }
    }

    // --- MODALES (Retornan HTML parcial) ---

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