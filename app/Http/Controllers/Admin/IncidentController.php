<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\Incident;
use App\Models\IncidentLog; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    /**
     * 1. CONSOLA DE OPERACIONES
     */
    public function console()
    {
        // A. Cola de Eventos Nuevos
        $pendingEvents = AlarmEvent::where('processed', false)
            ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
            ->orderBy('sia_codes.priority', 'desc') 
            ->orderBy('alarm_events.created_at', 'asc')
            ->select('alarm_events.*')
            ->with(['account.customer', 'siaCode'])
            ->get();

        // B. Mis Incidentes en Curso (Monitoreo / Espera)
        $myIncidents = Incident::where('operator_id', Auth::id() ?? 1)
            ->whereIn('status', ['in_progress', 'monitoring', 'police_dispatched'])
            ->with(['alarmEvent.account.customer', 'alarmEvent.siaCode'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.operations.console', compact('pendingEvents', 'myIncidents'));
    }

    /**
     * 2. TOMAR EVENTO (Crear Ticket)
     */
    public function take($eventId)
    {
        // Cargamos el evento Y la cuenta asociada para obtener su ID
        $event = AlarmEvent::with('account')->findOrFail($eventId);
        
        if ($event->processed) {
            return back()->with('error', 'Este evento ya fue atendido.');
        }

        // Crear Ticket (AQUÍ ESTABA EL ERROR: Faltaba alarm_account_id)
        $incident = Incident::create([
            'alarm_event_id'   => $event->id,
            'alarm_account_id' => $event->account->id, // <--- OBLIGATORIO PARA LA BD
            'customer_id'      => $event->account->customer_id ?? null,
            'operator_id'      => Auth::id() ?? 1,
            'status'           => 'in_progress',
            'started_at'       => now(),
        ]);

        // Marcar evento como procesado
        $event->update(['processed' => true, 'processed_at' => now()]);

        // BITÁCORA: Inicio de gestión
        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => 'Operador inició la atención del evento.'
        ]);

        return redirect()->route('admin.operations.manage', $incident->id);
    }

    /**
     * 3. GESTIONAR (Vista Detalle)
     */
    public function manage($id)
    {
        // 1. Cargar el Incidente con todas sus relaciones
        $incident = Incident::with([
            'alarmEvent.account.customer.contacts', // Contactos
            'alarmEvent.account.zones',             // Mapa de zonas
            'alarmEvent.siaCode',                   // Descripción del evento
            'logs.user'                             // Bitácora de operadores
        ])->findOrFail($id);

        // 2. CORRECCIÓN: Cargar el Historial de la Cuenta (Últimos 15 eventos)
        // Esto es lo que faltaba para que funcionara la pestaña "Historial"
        $accountHistory = AlarmEvent::where('account_number', $incident->alarmEvent->account_number)
            ->where('id', '!=', $incident->alarm_event_id) // Excluir el evento actual
            ->latest()
            ->take(15)
            ->get();

        return view('admin.operations.manage', compact('incident', 'accountHistory'));
    }

    /**
     * 4. PONER EN ESPERA (HOLD)
     */
    public function hold(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        $request->validate([
            'status' => 'required|in:monitoring,police_dispatched',
            'note'   => 'nullable|string'
        ]);

        $incident->update([
            'status' => $request->status,
        ]);

        // BITÁCORA: Registro de espera
        $statusLabel = $request->status == 'police_dispatched' ? 'Policía Despachada' : 'Monitoreo Preventivo';
        
        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'STATUS_CHANGE',
            'description' => "Incidente puesto en espera: $statusLabel. Nota: " . ($request->note ?? 'Sin observaciones')
        ]);

        return redirect()->route('operations.console')
            ->with('success', 'Incidente puesto en espera.');
    }

    /**
     * 5. CERRAR INCIDENTE
     */
    public function close(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        
        $request->validate([
            'resolution_notes' => 'required|string|min:5',
            'result_code'      => 'required|string'
        ]);

        $incident->update([
            'status'    => 'closed',
            'closed_at' => now(),
            'notes'     => $request->resolution_notes,
            'result'    => $request->result_code
        ]);

        // BITÁCORA: Cierre
        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => "Incidente cerrado. Resultado: {$request->result_code}. Informe: {$request->resolution_notes}"
        ]);

        return redirect()->route('operations.console')
            ->with('success', 'Incidente cerrado correctamente.');
    }
}