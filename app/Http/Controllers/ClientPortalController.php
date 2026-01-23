<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice; // Asegúrate de tener este modelo

class ClientPortalController extends Controller
{
    /**
     * El "Lienzo" del Mapa (Vista Principal del Cliente)
     */
    public function index()
    {
        // Retorna la vista contenedora del mapa (debes crear resources/views/client/map.blade.php)
        return view('client.map', [
            'user' => Auth::user()
        ]);
    }

    /**
     * API Interna: Devuelve JSON con todos los activos del cliente para pintar en el mapa.
     * Unifica Alarmas (Casas/Negocios) y GPS (Vehículos).
     */
    public function getAssets()
    {
        $user = Auth::user();
        
        // NOTA: Asumimos que el User tiene un campo 'email' que coincide con el 'email' del Customer
        // O que tienes una relación directa $user->customer. Ajusta según tu modelo real.
        
        // 1. Obtener Alarmas
        $alarms = AlarmAccount::whereHas('customer', function($q) use ($user) {
            $q->where('email', $user->email);
        })->get()->map(function($alarm) {
            return [
                'type' => 'alarm',
                'id' => $alarm->id,
                'lat' => $alarm->latitude,
                'lng' => $alarm->longitude,
                'status' => $alarm->monitoring_status, // armed, disarmed, alarm
                'name' => $alarm->branch_name ?? 'Propiedad',
                'address' => $alarm->installation_address
            ];
        });

        // 2. Obtener GPS
        $gps = GpsDevice::whereHas('customer', function($q) use ($user) {
            $q->where('email', $user->email);
        })->get()->map(function($device) {
            // Aquí deberías integrar la última posición real de Traccar si la tienes cacheada en DB
            return [
                'type' => 'gps',
                'id' => $device->id,
                'lat' => $device->last_latitude ?? 0, 
                'lng' => $device->last_longitude ?? 0,
                'status' => $device->status, // online, offline
                'name' => $device->name,
                'speed' => $device->speed ?? 0
            ];
        });

        return response()->json([
            'assets' => $alarms->merge($gps)
        ]);
    }

    /**
     * Modales HTML (Cargados vía AJAX al hacer clic en el mapa)
     */
    
    // Modal de Alarma
    public function modalAlarm($id)
    {
        $account = AlarmAccount::with(['zones', 'partitions'])->findOrFail($id);
        // Validar propiedad del usuario aquí (security check)
        return view('client.modals.alarm', compact('account'));
    }

    // Modal de GPS
    public function modalGps($id)
    {
        $device = GpsDevice::findOrFail($id);
        return view('client.modals.gps', compact('device'));
    }

    // Modal de Facturación
    public function modalBilling()
    {
        $user = Auth::user();
        $invoices = Invoice::whereHas('customer', function($q) use ($user) {
            $q->where('email', $user->email);
        })->orderBy('created_at', 'desc')->take(12)->get();

        return view('client.modals.billing', compact('invoices'));
    }
}