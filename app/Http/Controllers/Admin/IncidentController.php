<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    // 1. CONSOLA DE ESPERA
    public function console()
    {
        $pendingEvents = AlarmEvent::where('processed', false)
            ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
            ->orderBy('sia_codes.priority', 'desc') 
            ->orderBy('alarm_events.created_at', 'asc')
            ->select('alarm_events.*')
            ->with(['account.customer', 'siaCode']) // Asegura tener la relación siaCode en el modelo AlarmEvent
            ->get();

        return view('admin.operations.console', compact('pendingEvents'));
    }

    // 2. TOMAR EVENTO (Crear Ticket)
    public function take($eventId)
    {
        $event = AlarmEvent::findOrFail($eventId);
        
        if ($event->processed) {
            return back()->with('error', 'Este evento ya fue procesado.');
        }

        // Crear incidente vinculado
        $incident = Incident::create([
            'alarm_event_id' => $event->id,
            'customer_id' => $event->account->customer_id ?? null,
            // 'operator_id' => Auth::id(), // Activar cuando uses autenticación real
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Marcar evento como "En proceso" para sacarlo de la cola general
        $event->update(['processed' => true, 'processed_at' => now()]);

        return redirect()->route('admin.operations.manage', $incident->id);
    }

    // 3. GESTIONAR INCIDENTE (Pantalla de Atención)
    public function manage($id)
    {
        // Cargar incidente con todas las relaciones necesarias para el operador
        $incident = Incident::with([
            'alarmEvent.account.customer.contacts', // Contactos para llamar
            'alarmEvent.account.zones',             // Para ver qué zona es
            'alarmEvent.siaCode'                    // Qué significa el código
        ])->findOrFail($id);

        return view('admin.operations.manage', compact('incident'));
    }

    // 4. CERRAR INCIDENTE
    public function close(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        
        $request->validate([
            'resolution_notes' => 'required|string|min:5',
            'result_code' => 'required|string' // Ej: Falsa Alarma, Real, Prueba
        ]);

        $incident->update([
            'status' => 'closed',
            'closed_at' => now(),
            'notes' => $request->resolution_notes,
            'result' => $request->result_code
        ]);

        return redirect()->route('operations.console')
            ->with('success', 'Incidente cerrado correctamente.');
    }
}