<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\GpsDevice;

class DashboardController extends Controller
{
    public function index()
    {
        // Métricas en tiempo real
        $stats = [
            'active_alarms' => AlarmEvent::where('processed', false)->count(),
            'total_customers' => Customer::count(),
            'active_gps' => GpsDevice::count(), // Podrías filtrar por 'last_update' reciente
            'incidents_today' => Incident::whereDate('created_at', today())->count(),
        ];

        // Últimos 5 eventos críticos para visualización rápida
        $recentCritical = AlarmEvent::where('processed', false)
            ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
            ->where('sia_codes.priority', '>=', 4)
            ->orderBy('alarm_events.created_at', 'desc')
            ->select('alarm_events.*', 'sia_codes.description as sia_desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentCritical'));
    }
}