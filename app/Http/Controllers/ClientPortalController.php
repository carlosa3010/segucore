<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlarmAccount;
use App\Models\GpsDevice;
use App\Models\Invoice;

class ClientPortalController extends Controller
{
    /**
     * Vista principal: El Mapa
     */
    public function index()
    {
        $user = Auth::user();

        // Validación de seguridad: Si el usuario no tiene cliente asignado
        if (!$user->customer_id) {
            // Podrías redirigir a un error o mostrar una vista vacía
            return view('client.map', ['user' => $user, 'error' => 'No tienes una cuenta de cliente asociada.']);
        }

        return view('client.map', ['user' => $user]);
    }

    /**
     * API: Obtener activos del cliente vinculado
     */
    public function getAssets()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // 1. Verificar si el usuario tiene un cliente asignado
        if (!$user->customer_id) {
            return response()->json([
                'assets' => [],
                'message' => 'No tiene una cuenta de cliente vinculada.'
            ]);
        }

        // 2. Buscar Alarmas de ese cliente
        $alarms = \App\Models\AlarmAccount::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->get()
            ->map(function($alarm) {
                return [
                    'type' => 'alarm',
                    'id' => $alarm->id,
                    'lat' => $alarm->latitude,
                    'lng' => $alarm->longitude,
                    'status' => $alarm->monitoring_status ?? 'unknown', // armed, disarmed
                    'name' => $alarm->name ?? ('Cuenta: ' . $alarm->account_number),
                    'address' => $alarm->installation_address
                ];
            });

        // 3. Buscar GPS de ese cliente
        $gps = \App\Models\GpsDevice::where('customer_id', $user->customer_id)
            ->get()
            ->map(function($device) {
                return [
                    'type' => 'gps',
                    'id' => $device->id,
                    'lat' => $device->last_latitude ?? 0, 
                    'lng' => $device->last_longitude ?? 0,
                    'status' => $device->status ?? 'offline', // online, offline
                    'name' => $device->name ?? ('Dispositivo: ' . $device->imei),
                    'speed' => $device->speed ?? 0
                ];
            });

        return response()->json([
            'assets' => $alarms->merge($gps)
        ]);
    }

    // --- Modales ---

    public function modalAlarm($id)
    {
        $user = Auth::user();
        // Buscar la cuenta y asegurar que sea de ESTE cliente (Seguridad)
        $account = AlarmAccount::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->firstOrFail();

        return view('client.modals.alarm', compact('account'));
    }

    public function modalGps($id)
    {
        $user = Auth::user();
        $device = GpsDevice::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->firstOrFail();

        return view('client.modals.gps', compact('device'));
    }

    public function modalBilling()
    {
        $user = Auth::user();
        if (!$user->customer_id) return '<p>Sin datos</p>';

        $invoices = Invoice::where('customer_id', $user->customer_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('client.modals.billing', compact('invoices'));
    }
}