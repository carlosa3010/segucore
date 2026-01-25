<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;
use App\Models\DeviceAlert;
use App\Models\TraccarPosition; // Si usas Traccar
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Validación simple
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
            // Solo agregar si tiene coordenadas válidas
            if ($alarm->latitude && $alarm->longitude) {
                $assets->push([
                    'type' => 'alarm',
                    'id' => $alarm->id,
                    'lat' => (float)$alarm->latitude,
                    'lng' => (float)$alarm->longitude,
                    'status' => $alarm->monitoring_status ?? 'normal', // armed, disarmed, alarm
                    'name' => $alarm->name ?? $alarm->account_number,
                    'address' => $alarm->address,
                    'last_update' => $alarm->updated_at->diffForHumans(),
                ]);
            }
        }

        // 2. GPS
        $devices = GpsDevice::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->with('driver') // Cargar conductor para evitar consultas N+1
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
                'last_update' => Carbon::parse($device->last_connection)->format('H:i d/m'),
            ]);
        }

        return response()->json(['assets' => $assets]);
    }

    // --- MODALES ---

    public function modalGps($id)
    {
        $user = Auth::user();
        // Buscar dispositivo con seguridad
        $device = GpsDevice::with('driver')
            ->where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$device) return '<div class="p-4 text-red-500">Dispositivo no encontrado</div>';

        return view('client.modals.gps', compact('device'));
    }

    public function modalAlarm($id)
    {
        $user = Auth::user();
        $account = AlarmAccount::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$account) return '<div class="p-4 text-red-500">Cuenta no encontrada</div>';

        // Cargar últimas señales si tienes un modelo de eventos
        // $events = $account->events()->latest()->take(5)->get(); 

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
    
    // API Alertas (Simple)
    public function getLatestAlerts() {
        // ... (Mismo código anterior)
        return response()->json([]); 
    }
    
    // API Historial
    public function getHistory(Request $request, $id) {
         // ... (Mismo código anterior)
         return response()->json(['positions' => []]);
    }
}