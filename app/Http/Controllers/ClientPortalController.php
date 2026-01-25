<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;
use App\Models\DeviceAlert;
use App\Models\TraccarPosition; // Asegúrate de importar esto para el historial
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    /**
     * Vista principal: El Mapa
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->customer_id) {
            return view('client.map', ['user' => $user, 'error' => 'No tienes una cuenta de cliente asociada.']);
        }

        return view('client.map', ['user' => $user]);
    }

    /**
     * API: Obtener activos (Optimizado para polling)
     */
    public function getAssets()
    {
        $user = Auth::user();

        if (!$user || !$user->customer_id) {
            return response()->json(['assets' => []]);
        }

        // Alarmas
        $alarms = AlarmAccount::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->get()
            ->map(function($alarm) {
                return [
                    'type' => 'alarm',
                    'id' => $alarm->id,
                    'lat' => $alarm->latitude,
                    'lng' => $alarm->longitude,
                    'status' => $alarm->monitoring_status,
                    'name' => $alarm->account_number . ' - ' . $alarm->branch_name,
                    'last_update' => $alarm->updated_at->diffForHumans(),
                ];
            });

        // GPS (Incluyendo dirección y velocidad)
        $gps = GpsDevice::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->get()
            ->map(function($device) {
                return [
                    'type' => 'gps',
                    'id' => $device->id,
                    'lat' => $device->last_latitude, 
                    'lng' => $device->last_longitude,
                    'status' => $device->status, // online, offline
                    'name' => $device->name ?? $device->imei,
                    'speed' => round($device->speed, 1),
                    'course' => $device->course ?? 0, // Dirección para rotar icono
                    'ignition' => $device->ignition, // Si tienes este dato
                    'driver' => $device->driver ? $device->driver->first_name . ' ' . $device->driver->last_name : 'Sin Asignar',
                    'plate' => $device->plate_number ?? '---',
                    'last_update' => Carbon::parse($device->last_connection)->format('H:i:s d/m')
                ];
            });

        return response()->json([
            'assets' => $alarms->merge($gps)
        ]);
    }

    /**
     * API: Historial de Recorrido para el Cliente
     */
    public function getHistory(Request $request, $id)
    {
        $user = Auth::user();
        
        // Validación de seguridad
        $device = GpsDevice::where('id', $id)->where('customer_id', $user->customer_id)->firstOrFail();

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        // Límite de 3 días para evitar sobrecarga en cliente
        if($start->diffInDays($end) > 3) {
            return response()->json(['error' => 'El rango máximo es de 3 días'], 400);
        }

        $positions = TraccarPosition::where('device_id', $device->traccar_device_id) // Asumiendo relación con traccar
            ->whereBetween('device_time', [$start, $end])
            ->orderBy('device_time', 'asc')
            ->get(['latitude', 'longitude', 'speed', 'device_time', 'address']);

        return response()->json(['positions' => $positions]);
    }

    /**
     * API: Alertas recientes
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

    // --- Modales ---

    public function modalGps($id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->with(['driver', 'servicePlan']) // Cargar relaciones
            ->firstOrFail();

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