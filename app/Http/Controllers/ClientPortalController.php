<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\AlarmEvent; // Modelo de eventos
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
                    'plate' => '', 
                    'imei' => '',
                    'speed' => 0,
                    'course' => 0,
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
            $traccarData = TraccarDevice::where('uniqueid', $device->imei)->with('position')->first();
            
            $lat = $traccarData && $traccarData->position ? $traccarData->position->latitude : $device->last_latitude;
            $lng = $traccarData && $traccarData->position ? $traccarData->position->longitude : $device->last_longitude;
            $speed = $traccarData && $traccarData->position ? round($traccarData->position->speed * 1.852) : round($device->speed);
            $course = $traccarData && $traccarData->position ? $traccarData->position->course : $device->course;
            
            $lastUpdateRaw = $traccarData && $traccarData->position ? $traccarData->position->fixtime : $device->last_connection;
            $lastUpdate = $lastUpdateRaw ? Carbon::parse($lastUpdateRaw)->setTimezone('America/Caracas')->format('H:i d/m') : 'Sin Señal';

            $status = 'online';
            if ($lastUpdateRaw && Carbon::parse($lastUpdateRaw)->diffInHours() > 24) {
                $status = 'offline';
            }

            $assets->push([
                'type' => 'gps',
                'id' => $device->id,
                'lat' => (float)$lat,
                'lng' => (float)$lng,
                'status' => $traccarData ? $traccarData->status : $status,
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

    // --- MÉTODOS DE HISTORIAL Y REPORTES GPS (Sin cambios mayores) ---
    public function getHistory(Request $request, $id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->first();

        if (!$device) return response()->json(['error' => 'Dispositivo no encontrado'], 404);

        $traccarDevice = TraccarDevice::where('uniqueid', $device->imei)->first();
        if (!$traccarDevice) return response()->json(['error' => 'Sin sincronización.'], 400);

        $tz = 'America/Caracas';
        $start = Carbon::parse($request->start, $tz)->setTimezone('UTC');
        $end = Carbon::parse($request->end, $tz)->setTimezone('UTC');

        if ($start->diffInDays($end) > 31) return response()->json(['error' => 'Máximo 31 días.'], 400);

        $positions = TraccarPosition::where('deviceid', $traccarDevice->id)
            ->whereBetween('fixtime', [$start, $end])
            ->orderBy('fixtime', 'asc')
            ->select(['latitude', 'longitude', 'speed', 'fixtime as device_time', 'course'])
            ->get()
            ->map(function ($p) use ($tz) {
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

        $stats = [
            'distance_km' => 0, 'move_time' => 0, 'stop_time' => 0, 'off_time' => 0,
            'max_speed' => 0, 'avg_speed' => 0, 'trips' => 0
        ];

        $speedSum = 0; $speedCount = 0; $lastPos = null;

        foreach ($positions as $pos) {
            $speedKm = $pos->speed * 1.852;
            $attrs = is_string($pos->attributes) ? json_decode($pos->attributes, true) : $pos->attributes;
            $ignition = $attrs['ignition'] ?? false;
            
            if ($lastPos) {
                $timeDiff = Carbon::parse($pos->fixtime)->diffInSeconds(Carbon::parse($lastPos->fixtime));
                if ($timeDiff < 3600) {
                    $dist = isset($attrs['distance']) ? $attrs['distance'] : $this->calculateDistance($lastPos->latitude, $lastPos->longitude, $pos->latitude, $pos->longitude);
                    $stats['distance_km'] += ($dist / 1000);

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

        return $pdf->download("Reporte_Resumido_{$device->name}.pdf");
    }

    public function modalGps($id)
    {
        $user = Auth::user();
        $device = GpsDevice::with('driver')
            ->where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$device) return '<div class="p-6 text-center text-red-500">Dispositivo no disponible</div>';

        $traccarData = TraccarDevice::where('uniqueid', $device->imei)->with('position')->first();

        if ($traccarData && $traccarData->position) {
            $device->speed = round($traccarData->position->speed * 1.852);
            $device->last_connection = $traccarData->position->fixtime;
            $device->last_latitude = $traccarData->position->latitude;
            $device->last_longitude = $traccarData->position->longitude;
            
            $attributes = is_string($traccarData->position->attributes) 
                ? json_decode($traccarData->position->attributes, true) 
                : $traccarData->position->attributes;

            $device->ignition = $attributes['ignition'] ?? false;
            $device->odometer = isset($attributes['totalDistance']) ? round($attributes['totalDistance'] / 1000) : 0;
            $device->status = $traccarData->status;
        }

        return view('client.modals.gps', compact('device'));
    }

    public function sendCommand(Request $request, $id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Dispositivo no autorizado.']);
        }

        $type = $request->input('type');

        try {
            $response = $this->traccarService->sendCommand($device->imei, $type);
            if ($response && isset($response['id'])) {
                 return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'Fallo al enviar comando a Traccar.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
        }
    }

    // --- SECCIÓN CORREGIDA Y NUEVA ---

    // 7. MODAL ALARMA (CORREGIDO: alarm_account_id)
    public function modalAlarm($id)
    {
        $user = Auth::user();
        
        $alarm = AlarmAccount::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$alarm) return '<div class="p-4 text-red-500 text-center">Cuenta de alarma no encontrada.</div>';

        // ✅ CORRECCIÓN AQUÍ: Usamos 'alarm_account_id' en lugar de 'account_id'
        $events = AlarmEvent::where('alarm_account_id', $alarm->id)
                    ->with('siaCode')
                    ->latest()
                    ->take(10)
                    ->get();

        return view('client.modals.alarm', compact('alarm', 'events'));
    }

    // 8. MODAL FACTURACIÓN
    public function modalBilling()
    {
        $user = Auth::user();
        
        $invoices = Invoice::where('customer_id', $user->customer_id)
            ->orderBy('issue_date', 'desc')
            ->take(12) 
            ->get();

        return view('client.modals.billing', compact('invoices'));
    }

    // 9. ALERTAS RECIENTES
    public function getLatestAlerts()
    {
        $user = Auth::user();
        if (!$user->customer_id) return response()->json([]);

        $deviceIds = GpsDevice::where('customer_id', $user->customer_id)->pluck('id');

        $alerts = DeviceAlert::whereIn('gps_device_id', $deviceIds)
            ->with('gpsDevice:id,name') 
            ->where('created_at', '>=', Carbon::now()->subHours(48)) 
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($alert) {
                return [
                    'device' => $alert->gpsDevice->name ?? 'Desconocido',
                    'message' => $alert->message ?? $alert->type,
                    'time' => $alert->created_at->diffForHumans(),
                    'raw_time' => $alert->created_at->toIso8601String()
                ];
            });

        return response()->json($alerts);
    }

    // 10. DESCARGAR FACTURA
    public function downloadInvoice($id)
    {
        $user = Auth::user();
        
        $invoice = Invoice::with('customer')
            ->where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->firstOrFail();

        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'));
        
        return $pdf->download('Factura-' . $invoice->invoice_number . '.pdf');
    }

    // --- HELPERS ---
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) return 0;
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344) * 1000;
    }

    private function secondsToTime($seconds) {
        if ($seconds <= 0) return '0m';
        $dt = Carbon::now()->diff(Carbon::now()->addSeconds($seconds));
        return $dt->format('%dd %hh %im');
    }
}