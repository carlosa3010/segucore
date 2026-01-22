<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\GpsDevice;
use App\Models\DeviceAlert;
use App\Models\TraccarDevice;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Estadísticas de Clientes
        $totalCustomers = Customer::count();
        
        // 2. Incidentes de Monitoreo (Operaciones)
        $openIncidents = Incident::where('status', 'open')->count();
        $incidentsToday = Incident::whereDate('created_at', today())->count();

        // 3. Estado de la Flota GPS
        $totalDevices = GpsDevice::count();
        
        // Intentar conectar con Traccar para ver online/offline
        $onlineDevices = 0;
        try {
            $onlineDevices = TraccarDevice::where('status', 'online')->count();
        } catch (\Exception $e) {
            $onlineDevices = 0; // Si falla la conexión, asumimos 0
        }
        $offlineDevices = $totalDevices - $onlineDevices;

        // 4. Alertas de Seguridad (Últimas 24h)
        $recentAlerts = DeviceAlert::where('created_at', '>=', now()->subDay())->count();
        $unreadAlerts = DeviceAlert::whereNull('read_at')->count();

        // 5. Últimos 5 Eventos para el Feed
        $latestEvents = DeviceAlert::with('device')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalCustomers',
            'openIncidents',
            'incidentsToday',
            'totalDevices',
            'onlineDevices',
            'offlineDevices',
            'recentAlerts',
            'unreadAlerts',
            'latestEvents'
        ));
    }
}