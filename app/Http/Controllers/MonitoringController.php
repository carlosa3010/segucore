<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\SiaCode;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    // VENTANA 1: Lista de Eventos (Dashboard)
    public function index()
    {
        return view('monitoring.dashboard');
    }

    // VENTANA 2: Mapa Táctico
    public function map()
    {
        return view('monitoring.map');
    }

    // API COMÚN (Alimenta a ambas ventanas)
    public function getLiveEvents()
    {
        // ... (MANTÉN TODO EL CÓDIGO DE ESTA FUNCIÓN IGUAL QUE ANTES) ...
        $events = AlarmEvent::latest()
            ->take(50)
            ->get()
            ->map(function($event) {
                // ... (lógica existente) ...
                $account = AlarmAccount::where('account_number', $event->account_number)->with('customer')->first();
                $sia = SiaCode::where('code', $event->event_code)->first();
                $timeAgo = Carbon::parse($event->created_at)->diffForHumans();

                return [
                    'id' => $event->id,
                    'time_raw' => $event->created_at->format('H:i:s'),
                    'time_ago' => $timeAgo,
                    'account' => $event->account_number,
                    'customer_name' => $account ? $account->customer->first_name : 'DESCONOCIDO',
                    'zone' => $event->zone ?? 'N/A',
                    'code' => $event->event_code,
                    'description' => $sia ? $sia->description : 'Evento Sin Clasificar',
                    'color' => $sia ? $sia->color_hex : '#808080',
                    'priority' => $sia ? $sia->priority : 1,
                    'sound' => $sia ? $sia->sound_alert : null,
                    'status_icon' => $this->getIconByPriority($sia ? $sia->priority : 1),
                ];
            });
        return response()->json($events);
    }

    private function getIconByPriority($priority)
    {
        return match ($priority) {
            5 => '🔥', 4 => '🚨', 2 => '⚠️', 0 => '🔧', default => 'ℹ️',
        };
    }
}