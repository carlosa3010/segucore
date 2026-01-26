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
        // A. Cola de Eventos Nuevos (Pendientes de atención)
        $pendingEvents = AlarmEvent::with(['account.customer', 'siaCode']) // Carga las relaciones primero
        ->join('sia_codes', 'alarm_events.event_code', '=', 'sia_codes.code')
        ->where('alarm_events.processed', false)
        ->select('alarm_events.*') // Asegura que el ID sea el de alarm_events
        ->orderBy('sia_codes.priority', 'desc') 
        ->orderBy('alarm_events.created_at', 'asc')
        ->get();

        // B. Mis Incidentes en Curso (Tickets abiertos del operador)
        // CORRECCIÓN IMPORTANTE: Filtramos por todo lo que NO esté cerrado.
        // Así, los estados personalizados (ej: WAIT_CLIENT) siguen apareciendo.
        $myIncidents = Incident::where('operator_id', Auth::id() ?? 1)
            ->where('status', '!=', 'closed') 
            ->with(['alarmEvent.account.customer', 'alarmEvent.siaCode'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // C. Datos para Modal de Evento Manual
        $accounts = AlarmAccount::with('customer:id,first_name,last_name,business_name')
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
        // Cargamos siaCode para poder usar su descripción en el título
        $event = AlarmEvent::with(['account', 'siaCode'])->findOrFail($eventId);
        
        if ($event->processed) {
            return back()->with('error', 'Este evento ya fue atendido.');
        }

        // Crear Ticket
        $incident = Incident::create([
            // Título obligatorio basado en la descripción del código SIA
            'title'            => $event->siaCode ? $event->siaCode->description : 'Evento ' . $event->event_code,
            
            'alarm_event_id'   => $event->id,
            'alarm_account_id' => $event->account->id,
            'customer_id'      => $event->account->customer_id ?? null,
            'operator_id'      => Auth::id() ?? 1,
            'status'           => 'in_progress',
            
            // Usamos 'occurred_at' que es la columna real en la BD
            'occurred_at'      => now(), 
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
            'alarm_account_id' => $account->id, // Vinculación correcta
            'account_number'   => $account->account_number,
            'event_code'       => $request->event_code,
            'event_type'       => 'manual',
            'zone'             => '000', 
            'partition'        => '0',
            'ip_address'       => request()->ip(),
            'raw_data'         => "EVENTO MANUAL: " . $request->note,
            'received_at'      => now(),
            'processed'        => true, 
            'processed_at'     => now()
        ]);

        // B. Crear el Incidente vinculado
        $incident = Incident::create([
            'title'            => 'Manual: ' . $request->event_code,
            'alarm_event_id'   => $event->id,
            'alarm_account_id' => $account->id,
            'customer_id'      => $account->customer_id,
            'operator_id'      => Auth::id() ?? 1,
            'status'           => 'in_progress',
            'occurred_at'      => now(),
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
        // CORRECCIÓN: Usamos 'alarm_account_id' para ver también señales antiguas
        // que no tenían guardado el string 'account_number'.
        $accountHistory = AlarmEvent::where('alarm_account_id', $incident->alarm_account_id) // <--- CAMBIO AQUÍ
            ->where('id', '!=', $incident->alarm_event_id) // Excluir el evento actual
            ->latest()
            ->take(15)
            ->get();

        // Cargar Configuración Dinámica
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

        // Actualizamos el estado al código del motivo (ej: WAIT_CLIENT)
        $incident->update([
            'status' => $request->status,
        ]);

        // Obtener nombre legible del motivo para el log
        $reasonName = IncidentHoldReason::where('code', $request->status)->value('name') ?? $request->status;

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'STATUS_CHANGE',
            'description' => "Incidente puesto en espera: $reasonName. Nota: " . ($request->note ?? 'Sin observaciones')
        ]);

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

        // Guardamos fecha de resolución, cierre, notas e informe final
        $incident->update([
            'status'      => 'closed',
            'resolved_at' => now(),
            'closed_at'   => now(),
            'notes'       => $request->resolution_notes,
            'result'      => $request->result_code
        ]);

        // Obtener nombre legible de resolución para el log
        $resName = IncidentResolution::where('code', $request->result_code)->value('name') ?? $request->result_code;

        IncidentLog::create([
            'incident_id' => $incident->id,
            'user_id'     => Auth::id() ?? 1,
            'action_type' => 'SYSTEM',
            'description' => "Incidente cerrado. Resultado: $resName. Informe: {$request->resolution_notes}"
        ]);

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