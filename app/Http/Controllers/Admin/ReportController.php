<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\SiaCode;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * 1. PANTALLA PRINCIPAL DE REPORTES (BUSCADOR)
     */
    public function index(Request $request)
    {
        $query = AlarmEvent::with(['account.customer', 'siaCode', 'incident']);

        // --- FILTROS ---

        // 1. Por Cliente
        if ($request->filled('customer_id')) {
            $query->whereHas('account', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        // 2. Por Rango de Fechas
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 3. Por Código SIA (Tipo de Evento)
        if ($request->filled('sia_code')) {
            $query->where('event_code', $request->sia_code);
        }

        // 4. Por Estado (Procesado / Pendiente / Con Incidente)
        if ($request->filled('status')) {
            if ($request->status == 'incident') {
                $query->whereNotNull('incident_id'); // Solo los que generaron ticket
            } elseif ($request->status == 'auto') {
                $query->where('processed', true)->whereNull('incident_id'); // Procesados automáticamente
            } elseif ($request->status == 'pending') {
                $query->where('processed', false);
            }
        }

        // Resultados paginados
        $events = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Datos para los selectores del filtro (AQUÍ ESTABA EL ERROR, FALTABA ENVIAR ESTO)
        $customers = Customer::orderBy('first_name')->take(200)->get(); 
        $siaCodes = SiaCode::orderBy('code')->get();

        return view('admin.reports.index', compact('events', 'customers', 'siaCodes'));
    }

    /**
     * 2. GENERAR REPORTE IMPRESO POR CLIENTE
     */
    public function summary(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $customer = Customer::with('accounts')->findOrFail($request->customer_id);
        
        // Obtener todos los eventos del periodo
        $events = AlarmEvent::whereHas('account', function($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(), 
                Carbon::parse($request->date_to)->endOfDay()
            ])
            ->with(['siaCode', 'incident']) 
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular Estadísticas
        $stats = [
            'total' => $events->count(),
            'incidents' => $events->whereNotNull('incident')->count(),
            'auto' => $events->where('processed', true)->whereNull('incident')->count(),
            
            // Análisis de Resoluciones (si existe incidente)
            'false_alarms' => $events->filter(fn($e) => $e->incident && $e->incident->result == 'false_alarm')->count(),
            'real_alarms' => $events->filter(fn($e) => $e->incident && in_array($e->incident->result, ['real_police', 'real_medical', 'real_fire']))->count(),
        ];

        return view('admin.reports.print_summary', compact('customer', 'events', 'stats', 'request'));
    }

    /**
     * 3. GENERAR REPORTE DETALLADO DE UN INCIDENTE (FORENSE)
     */
    public function detail($id)
    {
        $incident = Incident::with([
            'alarmEvent.account.customer',
            'alarmEvent.siaCode',
            'alarmEvent.account.zones',
            'logs.user', 
            'operator'   
        ])->findOrFail($id);

        // Historial de contexto (10 eventos anteriores de esa cuenta)
        $history = AlarmEvent::where('account_number', $incident->alarmEvent->account_number)
            ->where('id', '<', $incident->alarm_event_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->with('siaCode')
            ->get();

        return view('admin.reports.print_detail', compact('incident', 'history'));
    }
}