<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\GpsDevice;
use App\Models\DeviceAlert;
use App\Models\TraccarDevice;
use App\Models\AlarmAccount; // <--- Importar
use App\Models\AlarmEvent;   // <--- Importar
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // --- A. GLOBALES ---
        $totalCustomers = Customer::count();
        $openIncidents = Incident::where('status', 'open')->count();

        // --- B. MONITOREO DE ALARMAS (KPIs) ---
        $totalAlarmAccounts = AlarmAccount::count();
        
        // Asumiendo que tienes un campo 'monitoring_status' o similar
        // Si no, usamos 'status' = 'active'
        $activePanels = AlarmAccount::where('status', 'active')->count();
        
        // Señales recibidas HOY (Tráfico de la central)
        $signalsToday = AlarmEvent::whereDate('created_at', today())->count();
        
        // Señales Críticas HOY (Pánicos, Robos, Fuego - Códigos SIA comunes)
        // 110=Fuego, 120=Pánico, 130=Robo, 100=Emergencia Médica
        $criticalSignals = AlarmEvent::whereDate('created_at', today())
            ->whereIn(DB::raw('LEFT(event_code, 3)'), ['110', '120', '130', '100']) // Ejemplo lógica SIA
            ->count();

        // --- C. RASTREO GPS (KPIs) ---
        $totalDevices = GpsDevice::count();
        $onlineDevices = 0;
        try {
            $onlineDevices = TraccarDevice::where('status', 'online')->count();
        } catch (\Exception $e) { }
        $offlineDevices = $totalDevices - $onlineDevices;

        // Alertas de Conducción (Últimas 24h)
        $gpsAlerts24h = DeviceAlert::where('created_at', '>=', now()->subDay())->count();

        // --- D. FEED UNIFICADO (Alarmas + GPS) ---
        // Combinamos las últimas alertas de GPS con los últimos eventos de Alarma
        // Esto es un poco avanzado, para simplificar por ahora mostramos 
        // dos listas o la lista de incidentes operativos.
        
        // Vamos a mostrar los últimos incidentes generados en Operaciones
        $latestIncidents = Incident::with('customer')
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