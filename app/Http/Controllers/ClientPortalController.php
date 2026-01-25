<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;
use App\Models\DeviceAlert;
use App\Models\TraccarPosition;
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->customer_id) {
            return view('client.map', ['user' => $user, 'error' => 'Sin cuenta asociada.']);
        }
        return view('client.map', ['user' => $user]);
    }

    public function getAssets()
    {
        $user = Auth::user();
        if (!$user || !$user->customer_id) return response()->json(['assets' => []]);

        // ALARMAS
        $alarms = AlarmAccount::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->get()
            ->map(function($alarm) {
                return [
                    'type' => 'alarm',
                    'id' => $alarm->id,
                    'lat' => (float) $alarm->latitude, // Casting importante para JS
                    'lng' => (float) $alarm->longitude,
                    'status' => $alarm->monitoring_status ?? 'unknown',
                    'name' => $alarm->account_number . ' - ' . ($alarm->branch_name ?? 'Principal'),
                    'last_update' => $alarm->updated_at->diffForHumans(),
                ];
            });

        // GPS
        $gps = GpsDevice::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->get()
            ->map(function($device) {
                return [
                    'type' => 'gps',
                    'id' => $device->id,
                    'lat' => (float) $device->last_latitude,
                    'lng' => (float) $device->last_longitude,
                    'status' => $device->status,
                    'name' => $device->name ?? 'Dispositivo GPS',
                    'speed' => round($device->speed, 0),
                    'course' => $device->course ?? 0,
                    'plate' => $device->plate_number ?? '---',
                    'driver' => optional($device->driver)->first_name ?? 'Sin Conductor', // Evita error si es null
                    'last_update' => Carbon::parse($device->last_connection)->format('H:i d/m')
                ];
            });

        return response()->json(['assets' => $alarms->merge($gps)]);
    }

    public function getHistory(Request $request, $id)
    {
        $user = Auth::user();
        // Validación estricta
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->first();
        
        if(!$device) return response()->json(['error' => 'Dispositivo no encontrado'], 404);

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        if($start->diffInDays($end) > 3) return response()->json(['error' => 'Máximo 3 días de consulta'], 400);

        // Simulando datos si no hay conexión a Traccar real aun, para que pruebes la UI
        // En producción usa tu modelo TraccarPosition real
        $positions = TraccarPosition::where('device_id', $device->traccar_device_id)
             ->whereBetween('device_time', [$start, $end])
             ->orderBy('device_time', 'asc')
             ->get(['latitude', 'longitude', 'speed', 'device_time']);

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
            ->map(function($alert) {
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

    // --- Modales Simplificados ---

    public function modalGps($id)
    {
        $user = Auth::user();
        $device = GpsDevice::with('driver')->where('id', $id)->where('customer_id', $user->customer_id)->firstOrFail();
        return view('client.modals.gps', compact('device'));
    }

    public function modalAlarm($id)
    {
        $user = Auth::user();
        $account = AlarmAccount::where('id', $id)->where('customer_id', $user->customer_id)->firstOrFail();
        return view('client.modals.alarm', compact('account'));
    }

    public function modalBilling()
    {
        $user = Auth::user();
        $invoices = Invoice::where('customer_id', $user->customer_id)->orderBy('created_at', 'desc')->take(5)->get();
        return view('client.modals.billing', compact('invoices'));
    }
}