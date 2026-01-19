<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\Incident;
use App\Models\IncidentLog; 
use App\Models\IncidentResolution; // Modelos de Config
use App\Models\IncidentHoldReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    /**
     * 1. CONSOLA DE OPERACIONES
     */
    public function console()
    {
        // ... (Tu código actual de la consola se mantiene igual) ...
        // A. Cola de Eventos Nuevos
        $pendingEvents = AlarmEvent::where('processed', false)
            ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
            ->orderBy('sia_codes.priority', 'desc') 
            ->orderBy('alarm_events.created_at', 'asc')
            ->select('alarm_events.*')
            ->with(['account.customer', 'siaCode'])
            ->get();

        // B. Mis Incidentes en Curso
        $myIncidents = Incident::where('operator_id', Auth::id() ?? 1)
            ->whereIn('status', ['in_progress', 'monitoring', 'police_dispatched'])
            ->with(['alarmEvent.account.customer', 'alarmEvent.siaCode'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.operations.console', compact('pendingEvents', 'myIncidents'));
    }

    /**
     * 2. TOMAR EVENTO
     */
    public function take($eventId)
    {
        $event = AlarmEvent::with('account')->findOrFail($eventId);
        
        if ($event->processed) {
            return back()->with('error', 'Este evento ya fue atendido.');
        }

        $incident = Incident::create([
            'alarm_event_id'   => $event->id,
            'alarm_account_id' => $event->account->id,
            'customer_id'      => $event->account->customer_id ?? null,
            'operator_id'      => Auth::id() ?? 1,
            'status'           => 'in_progress',
            'started_at'       => now(),
        ]);

        $event->update(['processed' => true, 'processed_at' => now()]);

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => 'Operador inició la atención del evento.'
        ]);

        // ✅ CORREGIDO: Ruta completa admin.operations.manage
        return redirect()->route('admin.operations.manage', $incident->id);
    }

    /**
     * 3. GESTIONAR (Vista Detalle)
     */
    public function manage($id)
    {
        $incident = Incident::with([
            'alarmEvent.account.customer.contacts',
            'alarmEvent.account.zones',
            'alarmEvent.siaCode',
            'logs.user' 
        ])->findOrFail($id);

        $accountHistory = AlarmEvent::where('account_number', $incident->alarmEvent->account_number)
            ->where('id', '!=', $incident->alarm_event_id)
            ->latest()
            ->take(15)
            ->get();

        $resolutions = IncidentResolution::where('is_active', true)->get();
        $holdReasons = IncidentHoldReason::where('is_active', true)->get();

        return view('admin.operations.manage', compact('incident', 'accountHistory', 'resolutions', 'holdReasons'));
    }

    /**
     * 4. PONER EN ESPERA (HOLD)
     */
    public function hold(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        // Permitimos que el motivo venga de la base de datos o sea un código duro
        $request->validate([
            'status' => 'required|string', 
            'note'   => 'nullable|string'
        ]);

        $incident->update([
            'status' => $request->status,
        ]);

        // Buscamos el nombre legible del motivo si existe en la BD
        $reasonName = IncidentHoldReason::where('code', $request->status)->value('name') ?? $request->status;

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'STATUS_CHANGE',
            'description' => "Incidente puesto en espera: $reasonName. Nota: " . ($request->note ?? 'Sin observaciones')
        ]);

        // ✅ CORREGIDO: Redirigir a 'admin.operations.console'
        return redirect()->route('admin.operations.console')
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

        // Buscamos nombre legible de resolución
        $resName = IncidentResolution::where('code', $request->result_code)->value('name') ?? $request->result_code;

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => "Incidente cerrado. Resultado: $resName. Informe: {$request->resolution_notes}"
        ]);

        // ✅ CORREGIDO: Redirigir a 'admin.operations.console'
        return redirect()->route('admin.operations.console')
            ->with('success', 'Incidente cerrado correctamente.');
    }

    /**
     * 6. AGREGAR NOTA (Bitácora Viva)
     */
    public function addNote(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $request->validate(['note' => 'required|string|min:2']);

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'NOTE',
            'description' => $request->note
        ]);

        return back()->with('success', 'Nota guardada.');
    }
}