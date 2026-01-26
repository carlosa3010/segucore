<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\GpsDevice;
use App\Models\DeviceAlert;
use App\Models\TraccarDevice;
use App\Models\AlarmAccount;
use App\Models\AlarmEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // --- A. GLOBALES ---
        $totalCustomers = Customer::count();
        $openIncidents = Incident::where('status', 'open')->count();

        // --- B. MONITOREO DE ALARMAS (KPIs) ---
        $totalAlarmAccounts = AlarmAccount::count();
        
        // CORRECCIÓN: Usamos 'service_status' en lugar de 'status'
        $activePanels = AlarmAccount::where('service_status', 'active')->count();
        
        // Señales recibidas HOY
        $signalsToday = AlarmEvent::whereDate('created_at', today())->count();
        
        // Señales Críticas HOY (Robo, Pánico, Fuego, Médica)
        // Buscamos eventos cuyo código empiece por 110 (Fuego), 120 (Pánico), 130 (Robo), etc.
        $criticalSignals = AlarmEvent::whereDate('created_at', today())
            ->where(function($q) {
                $q->where('event_code', 'like', '110%') // Fuego
                  ->orWhere('event_code', 'like', '120%') // Pánico
                  ->orWhere('event_code', 'like', '130%') // Robo
                  ->orWhere('event_code', 'like', '100%'); // Médica
            })
            ->count();

        // --- C. RASTREO GPS (KPIs) ---
        $totalDevices = GpsDevice::count();
        $onlineDevices = 0;
        try {
            // Intentamos obtener el conteo real de Traccar
            $onlineDevices = TraccarDevice::where('status', 'online')->count();
        } catch (\Exception $e) { 
            // Si falla la conexión (p.ej. credenciales mal), asumimos 0 para no romper la página
            $onlineDevices = 0;
        }
        $offlineDevices = $totalDevices - $onlineDevices;

        // Alertas de Conducción (Últimas 24h)
        $gpsAlerts24h = DeviceAlert::where('created_at', '>=', now()->subDay())->count();

        // --- D. FEED DE INCIDENTES ---
        // CORRECCIÓN AQUÍ: Cambiamos 'user' por 'operator' que es el nombre real de la relación en el Modelo
        $latestIncidents = Incident::with(['customer', 'operator']) 
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalCustomers', 'openIncidents',
            'totalAlarmAccounts', 'activePanels', 'signalsToday', 'criticalSignals',
            'totalDevices', 'onlineDevices', 'offlineDevices', 'gpsAlerts24h',
            'latestIncidents'
        ));
    }
}