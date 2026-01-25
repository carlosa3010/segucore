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
use Barryvdh\DomPDF\Facade\Pdf;

class ClientPortalController extends Controller
{
    protected $traccarService;

    public function __construct(TraccarApiService $traccarService)
    {
        $this->traccarService = $traccarService;
    }

    // --- VISTAS ---
    public function index()
    {
        $user = Auth::user();
        if (!$user->customer_id) {
            return view('client.map', ['user' => $user, 'error' => 'Sin cliente asignado.']);
        }
        return view('client.map', ['user' => $user]);
    }

    // --- API: LISTADO DE ACTIVOS (Actualización en tiempo real) ---
    public function getAssets()
    {
        $user = Auth::user();
        if (!$user->customer_id) return response()->json(['assets' => []]);

        $assets = collect();

        // 1. Alarmas
        $alarms = AlarmAccount::where('customer_id', $user->customer_id)->where('is_active', true)->get();
        foreach ($alarms as $alarm) {
            if ($alarm->latitude && $alarm->longitude) {
                $assets->push([
                    'type' => 'alarm',
                    'id' => $alarm->id,
                    'lat' => (float)$alarm->latitude,
                    'lng' => (float)$alarm->longitude,
                    'status' => $alarm->monitoring_status ?? 'normal',
                    'name' => $alarm->name ?? $alarm->account_number,
                    'plate' => $alarm->account_number, // Para búsqueda
                    'imei' => '', 
                    'last_update' => $alarm->updated_at->diffForHumans(),
                ]);
            }
        }

        // 2. GPS (Consulta Híbrida: Local + Traccar)
        $devices = GpsDevice::where('customer_id', $user->customer_id)->where('is_active', true)->with('driver')->get();

        foreach ($devices as $device) {
            // Datos por defecto (Local)
            $lat = $device->last_latitude;
            $lng = $device->last_longitude;
            $speed = round($device->speed);
            $course = $device->course;
            $lastUpdateRaw = $device->last_connection;
            $status = $device->status;

            // Intentar obtener datos frescos de Traccar
            $traccarData = TraccarDevice::where('uniqueid', $device->imei)->with('position')->first();
            
            if ($traccarData && $traccarData->position) {
                $lat = $traccarData->position->latitude;
                $lng = $traccarData->position->longitude;
                $speed = round($traccarData->position->speed * 1.852);
                $course = $traccarData->position->course;
                $lastUpdateRaw = $traccarData->position->fixtime;
                $status = $traccarData->status; // online/offline/unknown
            }

            // Lógica de estado visual
            if ($lastUpdateRaw && Carbon::parse($lastUpdateRaw)->diffInHours() > 24) {
                $status = 'offline';
            } elseif ($speed > 2) {
                $status = 'moving';
            }

            $lastUpdate = $lastUpdateRaw ? Carbon::parse($lastUpdateRaw)->setTimezone('America/Caracas')->format('H:i d/m') : 'Sin Señal';

            $assets->push([
                'type' => 'gps',
                'id' => $device->id,
                'lat' => (float)$lat,
                'lng' => (float)$lng,
                'status' => $status,
                'name' => $device->name,
                'plate' => $device->plate_number ?? '',
                'imei' => $device->imei ?? '',
                'speed' => $speed,
                'course' => $course ?? 0,
                'last_update' => $lastUpdate,
            ]);
        }

        return response()->json(['assets' => $assets]);
    }

    // --- API: HISTORIAL ---
    public function getHistory(Request $request, $id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->first();

        if (!$device) return response()->json(['error' => 'Dispositivo no encontrado'], 404);

        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if (!$traccarDevice) return response()->json(['error' => 'Sin sincronización con satélite.'], 400);

        $tz = 'America/Caracas';
        $start = Carbon::parse($request->start, $tz)->setTimezone('UTC');
        $end = Carbon::parse($request->end, $tz)->setTimezone('UTC');

        if ($start->diffInDays($end) > 31) return response()->json(['error' => 'Rango máximo: 31 días.'], 400);

        $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
            ->whereBetween('fixtime', [$start, $end])
            ->orderBy('fixtime', 'asc')
            ->select(['latitude', 'longitude', 'speed', 'fixtime as device_time', 'course'])
            ->limit(5000) // Límite de seguridad
            ->get()
            ->map(function ($p) use ($tz) {
                return [
                    'latitude' => $p->latitude,
                    'longitude' => $p->longitude,
                    'speed' => round($p->speed * 1.852), // Convertir nudos a km/h
                    'course' => $p->course,
                    'device_time' => Carbon::parse($p->device_time)->setTimezone($tz)->toIso8601String()
                ];
            });

        return response()->json(['positions' => $positions]);
    }

    // --- FUNCIONALIDAD: REPORTE PDF ---
    public function downloadReport(Request $request, $id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->firstOrFail();
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->firstOrFail();

        $tz = 'America/Caracas';
        $start = Carbon::parse($request->start, $tz)->setTimezone('UTC');
        $end = Carbon::parse($request->end, $tz)->setTimezone('UTC');

        $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
            ->whereBetween('fixtime', [$start, $end])
            ->orderBy('fixtime', 'asc')
            ->get(['fixtime', 'speed', 'attributes', 'latitude', 'longitude']);

        if ($positions->isEmpty()) {
            return back()->with('error', 'No hay datos en este período.');
        }

        // Estadísticas
        $stats = [
            'distance_km' => 0, 'move_time' => 0, 'stop_time' => 0, 
            'off_time' => 0, 'max_speed' => 0, 'avg_speed' => 0
        ];

        $speedSum = 0; $speedCount = 0; $lastPos = null;

        foreach ($positions as $pos) {
            $speedKm = $pos->speed * 1.852;
            $attrs = is_string($pos->attributes) ? json_decode($pos->attributes, true) : $pos->attributes;
            $ignition = $attrs['ignition'] ?? false;

            if ($lastPos) {
                $timeDiff = Carbon::parse($pos->fixtime)->diffInSeconds(Carbon::parse($lastPos->fixtime));
                
                if ($timeDiff < 3600) { // Ignorar saltos grandes
                    // Distancia
                    $dist = isset($attrs['distance']) ? $attrs['distance'] : $this->calculateDistance($lastPos->latitude, $lastPos->longitude, $pos->latitude, $pos->longitude);
                    $stats['distance_km'] += ($dist / 1000);

                    // Tiempos
                    if ($ignition) {
                        if ($speedKm > 2) $stats['move_time'] += $timeDiff;
                        else $stats['stop_time'] += $timeDiff;
                    } else {
                        $stats['off_time'] += $timeDiff;
                    }
                }
            }

            if ($speedKm > $stats['max_speed']) $stats['max_speed'] = $speedKm;
            if ($speedKm > 5) { $speedSum += $speedKm; $speedCount++; }
            $lastPos = $pos;
        }

        $stats['avg_speed'] = $speedCount > 0 ? round($speedSum / $speedCount) : 0;
        $stats['distance_km'] = round($stats['distance_km'], 2);
        
        $formattedStats = [
            'move_str' => $this->secondsToTime($stats['move_time']),
            'stop_str' => $this->secondsToTime($stats['stop_time']),
            'off_str'  => $this->secondsToTime($stats['off_time']),
            'total_engine_str' => $this->secondsToTime($stats['move_time'] + $stats['stop_time']),
            'distance' => $stats['distance_km'],
            'max_speed' => round($stats['max_speed']),
            'avg_speed' => $stats['avg_speed']
        ];

        $pdf = Pdf::loadView('client.pdf.summary', [
            'device' => $device,
            'start' => $start->setTimezone($tz),
            'end' => $end->setTimezone($tz),
            'stats' => $formattedStats
        ]);

        return $pdf->download("Reporte_{$device->name}.pdf");
    }

    // --- API: COMANDOS ---
    public function sendCommand(Request $request, $id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->firstOrFail();
        
        $request->validate(['type' => 'required|in:engineStop,engineResume']);

        // Buscar en Traccar por IMEI
        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if (!$traccarDevice) return response()->json(['success' => false, 'message' => 'Dispositivo no vinculado.'], 400);

        try {
            $success = $this->traccarService->sendCommand($traccarDevice->id, $request->type);
            
            if ($success) return response()->json(['success' => true, 'message' => 'Comando enviado con éxito.']);
            else return response()->json(['success' => false, 'message' => 'El comando fue rechazado por el servidor.'], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // --- API: ALERTAS ---
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

    // --- MODALES (HTML) ---

    public function modalGps($id)
    {
        $user = Auth::user();
        $device = GpsDevice::with('driver')->where('id', $id)->where('customer_id', $user->customer_id)->first();

        if (!$device) return '<div class="p-6 text-red-500">No encontrado</div>';

        // Inyección de Datos Reales
        $traccarData = TraccarDevice::where('uniqueid', $device->imei)->with('position')->first();

        if ($traccarData && $traccarData->position) {
            $device->speed = round($traccarData->position->speed * 1.852);
            $device->last_connection = $traccarData->position->fixtime;
            
            $attrs = is_string($traccarData->position->attributes) ? json_decode($traccarData->position->attributes, true) : $traccarData->position->attributes;
            $device->ignition = $attrs['ignition'] ?? false;
            $device->odometer = isset($attrs['totalDistance']) ? round($attrs['totalDistance'] / 1000) : 0;
            $device->status = (Carbon::parse($traccarData->position->fixtime)->diffInHours() > 24) ? 'offline' : ($device->speed > 2 ? 'moving' : 'online');
        }

        return view('client.modals.gps', compact('device'));
    }

    public function modalAlarm($id)
    {
        $user = Auth::user();
        $account = AlarmAccount::where('id', $id)->where('customer_id', $user->customer_id)->first();
        if (!$account) return '<div class="p-6 text-red-500">No encontrado</div>';
        return view('client.modals.alarm', compact('account'));
    }

    public function modalBilling()
    {
        $user = Auth::user();
        $invoices = Invoice::where('customer_id', $user->customer_id)->latest()->take(5)->get();
        return view('client.modals.billing', compact('invoices'));
    }

    // --- HELPERS PRIVADOS ---
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        return ($dist * 60 * 1.1515 * 1.609344) * 1000;
    }

    private function secondsToTime($seconds) {
        $dt = Carbon::now()->diff(Carbon::now()->addSeconds($seconds));
        return $dt->format('%dd %hh %im');
    }
}