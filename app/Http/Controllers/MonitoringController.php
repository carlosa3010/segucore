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

    /**
     * API que alimenta el Video Wall.
     * Ahora ordena por Prioridad (SIA) y luego por Recencia.
     */
    public function getLiveEvents()
    {
        $events = AlarmEvent::query()
            // 1. Unimos con la tabla de códigos SIA para obtener la prioridad
            ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
            // 2. Filtramos: Solo eventos que NO han sido gestionados (processed = false)
            ->where('alarm_events.processed', false)
            // 3. ORDEN CRÍTICO: 
            // Primero las prioridades más altas (5, 4, 3...)
            ->orderBy('sia_codes.priority', 'desc') 
            // Luego lo más nuevo dentro de esa misma prioridad
            ->orderBy('alarm_events.created_at', 'desc')
            // Seleccionamos los campos para evitar conflictos de ID entre tablas
            ->select('alarm_events.*', 'sia_codes.priority', 'sia_codes.color_hex', 'sia_codes.sound_alert')
            ->take(50)
            ->get()
            ->map(function($event) {
                // Buscamos la cuenta y el cliente vinculado
                $account = AlarmAccount::where('account_number', $event->account_number)
                    ->with('customer')
                    ->first();
                
                $sia = SiaCode::where('code', $event->event_code)->first();
                $timeAgo = Carbon::parse($event->created_at)->diffForHumans();

                return [
                    'id' => $event->id,
                    'time_raw' => $event->created_at->format('H:i:s'),
                    'time_ago' => $timeAgo,
                    'account' => $event->account_number,
                    'customer_name' => $account && $account->customer ? $account->customer->first_name : 'DESCONOCIDO',
                    'zone' => $event->zone ?? 'N/A',
                    'code' => $event->event_code,
                    'description' => $sia ? $sia->description : 'Evento Sin Clasificar',
                    'color' => $sia ? $sia->color_hex : '#808080',
                    'priority' => $event->priority,
                    'sound' => $event->sound_alert,
                    'status_icon' => $this->getIconByPriority($event->priority),
                ];
            });

        return response()->json($events);
    }

    private function getIconByPriority($priority)
    {
        return match ($priority) {
            5 => '🔥', // Pánico / Emergencia
            4 => '🚨', // Robo / Intrusión
            3 => '⚠️', // Fallo Técnico
            2 => 'ℹ️', // Apertura/Cierre
            default => '🔧', // Tests / Otros
        };
    }
}