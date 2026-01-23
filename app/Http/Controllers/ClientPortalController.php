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

        // Si el usuario no tiene cliente asignado, devolver vacío (evita error 500)
        if (!$user || !$user->customer_id) {
            return response()->json(['assets' => []]);
        }

        // Buscar Alarmas
        $alarms = \App\Models\AlarmAccount::where('customer_id', $user->customer_id)
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
                ];
            });

        // Buscar GPS
        $gps = \App\Models\GpsDevice::where('customer_id', $user->customer_id)
            ->where('is_active', true)
            ->get()
            ->map(function($device) {
                return [
                    'type' => 'gps',
                    'id' => $device->id,
                    'lat' => $device->last_latitude, 
                    'lng' => $device->last_longitude,
                    'status' => $device->status,
                    'name' => $device->name ?? $device->imei,
                    'speed' => $device->speed,
                    'driver' => $device->driver ? $device->driver->first_name : 'Sin conductor'
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