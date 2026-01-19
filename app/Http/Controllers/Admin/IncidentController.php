<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    // La pantalla principal de atención (Cola de espera)
    public function console()
    {
        // Traemos eventos NO procesados ordenados por prioridad
        $pendingEvents = AlarmEvent::where('processed', false)
            ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
            ->orderBy('sia_codes.priority', 'desc') // 5 Pánico primero
            ->orderBy('alarm_events.created_at', 'asc') // Los más viejos primero (FIFO)
            ->select('alarm_events.*')
            ->with('account.customer') // Eager loading para ver quién es
            ->get();

        return view('admin.operations.console', compact('pendingEvents'));
    }

    // Acción: Operador toma el evento ("Atender")
    public function take($eventId)
    {
        $event = AlarmEvent::findOrFail($eventId);
        
        // Verificar si ya fue tomado por otro
        if ($event->processed) {
            return back()->with('error', 'Este evento ya fue atendido.');
        }

        // Crear el Incidente (Ticket)
        $incident = Incident::create([
            'alarm_event_id' => $event->id,
            'customer_id' => $event->account->customer_id ?? null, // Asumimos relación
            // 'operator_id' => Auth::id(), // Descomentar cuando tengas Auth
            'status' => 'open',
            'started_at' => now(),
        ]);

        // Redirigir a la pantalla de gestión del incidente
        return redirect()->route('admin.operations.manage', $incident->id);
    }
}