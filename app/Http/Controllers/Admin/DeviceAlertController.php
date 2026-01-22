<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceAlert;
use Illuminate\Http\Request;

class DeviceAlertController extends Controller
{
    public function index()
    {
        // Alertas con sus dispositivos y clientes, ordenadas por fecha
        $alerts = DeviceAlert::with('device.customer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Marcar todas como leídas al entrar (lógica simple)
        DeviceAlert::whereNull('read_at')->update(['read_at' => now()]);

        return view('admin.alerts.index', compact('alerts'));
    }
}