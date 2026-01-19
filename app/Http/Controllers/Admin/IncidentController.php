<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\Incident;
use App\Models\IncidentLog;
use App\Models\IncidentResolution;
use App\Models\IncidentHoldReason;
use App\Models\SiaCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    /**
     * 1. CONSOLA DE OPERACIONES
     */
    public function console()
    {
        // A. Cola de Eventos Nuevos (Pendientes)
        $pendingEvents = AlarmEvent::where('processed', false)
            ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
            ->orderBy('sia_codes.priority', 'desc') 
            ->orderBy('alarm_events.created_at', 'asc')
            ->select('alarm_events.*')
            ->with(['account.customer', 'siaCode'])
            ->get();

        // B. Mis Incidentes en Curso (Tickets abiertos del operador)
        $myIncidents = Incident::where('operator_id', Auth::id() ?? 1)
            ->whereIn('status', ['in_progress', 'monitoring', 'police_dispatched'])
            ->with(['alarmEvent.account.customer', 'alarmEvent.siaCode'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // C. Datos para Modal de Evento Manual (Optimizado)
        $accounts = AlarmAccount::with('customer:id,full_name,business_name')
            ->select('id', 'customer_id', 'account_number', 'branch_name')
            ->get();
            
        $siaCodes = SiaCode::orderBy('priority', 'desc')->get();

        return view('admin.operations.console', compact('pendingEvents', 'myIncidents', 'accounts', 'siaCodes'));
    }

    /**
     * 2. TOMAR EVENTO (Crear Ticket desde Señal Real)
     */
    public function take($eventId)
    {
        $event = AlarmEvent::with('account')->findOrFail($eventId);
        
        if ($event->processed) {
            return back()->with('error', 'Este evento ya fue atendido.');
        }

        // Crear Ticket
        $incident = Incident::create([
            'alarm_event_id'   => $event->id,
            'alarm_account_id' => $event->account->id,
            'customer_id'      => $event->account->customer_id ?? null,
            'operator_id'      => Auth::id() ?? 1,
            'status'           => 'in_progress',
            'started_at'       => now(),
        ]);

        // Marcar evento como procesado
        $event->update(['processed' => true, 'processed_at' => now()]);

        // Log Inicial
        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => 'Operador inició la atención del evento.'
        ]);

        return redirect()->route('admin.operations.manage', $incident->id);
    }

    /**
     * 3. CREAR EVENTO MANUAL (Ticket sin Señal)
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:alarm_accounts,id',
            'event_code' => 'required|exists:sia_codes,code',
            'note'       => 'required|string|min:5'
        ]);

        $account = AlarmAccount::find($request->account_id);

        // A. Crear un "Evento Artificial"
        $event = AlarmEvent::create([
            'account_number' => $account->account_number,
            'event_code'     => $request->event_code,
            'event_type'     => 'manual',
            'zone'           => '000', // Zona genérica
            'partition'      => '0',
            'ip_address'     => request()->ip(),
            'raw_data'       => "EVENTO MANUAL: " . $request->note,
            'received_at'    => now(),
            'processed'      => true, // Ya nace procesado
            'processed_at'   => now()
        ]);

        // B. Crear el Incidente vinculado
        $incident = Incident::create([
            'alarm_event_id'   => $event->id,
            'alarm_account_id' => $account->id,
            'customer_id'      => $account->customer_id,
            'operator_id'      => Auth::id() ?? 1,
            'status'           => 'in_progress',
            'started_at'       => now(),
        ]);

        // C. Bitácora
        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => "Incidente creado MANUALMENTE. Motivo: " . $request->note
        ]);

        return redirect()->route('admin.operations.manage', $incident->id);
    }

    /**
     * 4. GESTIONAR (Vista Detalle de Atención)
     */
    public function manage($id)
    {
        $incident = Incident::with([
            'alarmEvent.account.customer.contacts',
            'alarmEvent.account.zones',
            'alarmEvent.siaCode',
            'logs.user' 
        ])->findOrFail($id);

        // Historial reciente de la cuenta
        $accountHistory = AlarmEvent::where('account_number', $incident->alarmEvent->account_number)
            ->where('id', '!=', $incident->alarm_event_id)
            ->latest()
            ->take(15)
            ->get();

        // Cargar Configuración Dinámica (Resoluciones y Motivos)
        $resolutions = IncidentResolution::where('is_active', true)->get();
        $holdReasons = IncidentHoldReason::where('is_active', true)->get();

        return view('admin.operations.manage', compact('incident', 'accountHistory', 'resolutions', 'holdReasons'));
    }

    /**
     * 5. PONER EN ESPERA (HOLD)
     */
    public function hold(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        $request->validate([
            'status' => 'required|string', 
            'note'   => 'nullable|string'
        ]);

        $incident->update([
            'status' => $request->status,
        ]);

        // Obtener nombre legible del motivo
        $reasonName = IncidentHoldReason::where('code', $request->status)->value('name') ?? $request->status;

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'STATUS_CHANGE',
            'description' => "Incidente puesto en espera: $reasonName. Nota: " . ($request->note ?? 'Sin observaciones')
        ]);

        // Redirigir a la Consola
        return redirect()->route('admin.operations.console')
            ->with('success', 'Incidente puesto en espera.');
    }

    /**
     * 6. CERRAR INCIDENTE
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

        // Obtener nombre legible de resolución
        $resName = IncidentResolution::where('code', $request->result_code)->value('name') ?? $request->result_code;

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => "Incidente cerrado. Resultado: $resName. Informe: {$request->resolution_notes}"
        ]);

        // Redirigir a la Consola
        return redirect()->route('admin.operations.console')
            ->with('success', 'Incidente cerrado correctamente.');
    }

    /**
     * 7. AGREGAR NOTA (Bitácora Viva)
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