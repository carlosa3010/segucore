<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;
use App\Models\DeviceAlert;
use App\Models\TraccarPosition;
use App\Models\TraccarDevice; 
use App\Services\TraccarApiService;
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    protected $traccarService;

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

    /**
     * API: Obtener activos con datos EN VIVO de Traccar
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
                    'last_update' => $alarm->updated_at->diffForHumans(),
                ]);
            }
        }

        // 2. GPS (Consulta Híbrida: Local + Traccar Live)
        $devices = GpsDevice::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->with('driver')
            ->get();

        foreach ($devices as $device) {
            // Buscar datos en vivo por IMEI
            $traccarData = TraccarDevice::where('uniqueid', $device->imei)->with('position')->first();
            
            // Determinar datos reales
            $lat = $traccarData && $traccarData->position ? $traccarData->position->latitude : $device->last_latitude;
            $lng = $traccarData && $traccarData->position ? $traccarData->position->longitude : $device->last_longitude;
            $speed = $traccarData && $traccarData->position ? round($traccarData->position->speed * 1.852) : round($device->speed);
            $course = $traccarData && $traccarData->position ? $traccarData->position->course : $device->course;
            
            // Hora corregida a Zona Horaria VZLA
            $lastUpdateRaw = $traccarData && $traccarData->position ? $traccarData->position->fixtime : $device->last_connection;
            $lastUpdate = $lastUpdateRaw ? Carbon::parse($lastUpdateRaw)->setTimezone('America/Caracas')->format('H:i d/m') : 'Sin Señal';

            // Estado (Si tiene más de 24h sin reporte, es offline)
            $status = 'online';
            if ($lastUpdateRaw && Carbon::parse($lastUpdateRaw)->diffInHours() > 24) {
                $status = 'offline';
            }

            $assets->push([
                'type' => 'gps',
                'id' => $device->id,
                'lat' => (float)$lat,
                'lng' => (float)$lng,
                'status' => $traccarData ? $traccarData->status : $status, // status real de traccar
                'name' => $device->name,
                'speed' => $speed,
                'course' => $course ?? 0,
                'last_update' => $lastUpdate,
            ]);
        }

        return response()->json(['assets' => $assets]);
    }

    // --- MODALES (Con inyección de datos reales) ---

    public function modalGps($id)
    {
        $user = Auth::user();
        $device = GpsDevice::with('driver')
            ->where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$device) return '<div class="p-6 text-center text-red-500">Dispositivo no disponible</div>';

        // INYECCIÓN DE DATOS REALES (TRACCAR)
        $traccarData = TraccarDevice::where('uniqueid', $device->imei)->with('position')->first();

        if ($traccarData && $traccarData->position) {
            // Sobreescribimos los datos del modelo local con los frescos de Traccar
            $device->speed = round($traccarData->position->speed * 1.852); // Nudos a Km/h
            $device->last_connection = $traccarData->position->fixtime;
            $device->last_latitude = $traccarData->position->latitude;
            $device->last_longitude = $traccarData->position->longitude;
            
            // Extraer atributos JSON (Odómetro, Ignición)
            $attributes = is_string($traccarData->position->attributes) 
                ? json_decode($traccarData->position->attributes, true) 
                : $traccarData->position->attributes;

            $device->ignition = $attributes['ignition'] ?? false;
            $device->odometer = isset($attributes['totalDistance']) ? round($attributes['totalDistance'] / 1000) : 0;
            $device->status = $traccarData->status; // online/offline/unknown
        }

        return view('client.modals.gps', compact('device'));
    }

    public function modalAlarm($id)
    {
        $user = Auth::user();
        $account = AlarmAccount::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();
        return view('client.modals.alarm', compact('account'));
    }

    public function modalBilling()
    {
        $user = Auth::user();
        $invoices = Invoice::where('customer_id', $user->customer_id)->latest()->take(5)->get();
        return view('client.modals.billing', compact('invoices'));
    }

    // --- FUNCIONES API (Historial y Comandos) ---

    public function getHistory(Request $request, $id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->first();

        if (!$device) return response()->json(['error' => 'Dispositivo no encontrado'], 404);

        // 1. Obtener ID real de Traccar
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if (!$traccarDevice) return response()->json(['error' => 'Sin sincronización con satélite.'], 400);

        // 2. Parsear fechas (Asumiendo que el input viene en hora VZLA, convertir a UTC para consulta)
        $tz = 'America/Caracas';
        $start = Carbon::parse($request->start, $tz)->setTimezone('UTC');
        $end = Carbon::parse($request->end, $tz)->setTimezone('UTC');

        if ($start->diffInDays($end) > 3) return response()->json(['error' => 'Máximo 3 días.'], 400);

        // 3. Consultar Posiciones
        $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
            ->whereBetween('fixtime', [$start, $end])
            ->orderBy('fixtime', 'asc')
            ->select(['latitude', 'longitude', 'speed', 'fixtime as device_time', 'course'])
            ->get()
            ->map(function ($p) use ($tz) {
                // Formatear respuesta para JS
                return [
                    'latitude' => $p->latitude,
                    'longitude' => $p->longitude,
                    'speed' => round($p->speed * 1.852),
                    'course' => $p->course,
                    'device_time' => Carbon::parse($p->device_time)->setTimezone($tz)->toIso8601String()
                ];
            });

        return response()->json(['positions' => $positions]);
    }

    public function sendCommand(Request $request, $id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->firstOrFail();
        
        $request->validate(['type' => 'required|in:engineStop,engineResume']);

        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if (!$traccarDevice) return response()->json(['success' => false, 'message' => 'Error de ID Traccar'], 400);

        try {
            // Envío Real
            $success = $this->traccarService->sendCommand($traccarDevice->id, $request->type);
            return response()->json(['success' => $success, 'message' => $success ? 'Comando Enviado' : 'Falló el envío']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error técnico: ' . $e->getMessage()], 500);
        }
    }
    
    public function getLatestAlerts() {
        // (Tu código de alertas existente aquí...)
        return response()->json([]); 
    }
}