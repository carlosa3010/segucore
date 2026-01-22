<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\AlarmAccount;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\SiaCode;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * 1. LISTADO GENERAL CON FILTROS
     */
    public function index(Request $request)
    {
        $query = AlarmEvent::with(['account.customer', 'siaCode', 'incident']);

        // Filtro por Cliente
        if ($request->filled('customer_id')) {
            $query->whereHas('account', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        // Filtro por Cuenta (Abonado)
        if ($request->filled('account_id')) {
            $query->where('alarm_account_id', $request->account_id);
        }

        // Filtro de Fechas
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtro por Estado de Proceso
        if ($request->filled('status')) {
            if ($request->status == 'processed') {
                $query->where('processed', true);
            } elseif ($request->status == 'pending') {
                $query->where('processed', false);
            }
        }

        // Filtro por Prioridad (Tipo)
        if ($request->filled('priority')) {
            $query->whereHas('siaCode', function($q) use ($request) {
                $q->where('priority', '>=', $request->priority);
            });
        }

        $events = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Cargar listas para los selects
        $customers = Customer::orderBy('first_name')->take(100)->get();
        
        return view('admin.reports.index', compact('events', 'customers'));
    }

    /**
     * 2. REPORTE RESUMEN POR CLIENTE (Imprimible)
     */
    public function summary(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $customer = Customer::with('accounts')->findOrFail($request->customer_id);
        
        // Obtener eventos del rango
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

        // Estadísticas para el resumen
        $stats = [
            'total' => $events->count(),
            'processed' => $events->where('processed', true)->count(),
            'incidents' => $events->whereNotNull('incident')->count(),
            'false_alarms' => $events->filter(fn($e) => $e->incident && $e->incident->result == 'false_alarm')->count(),
            'real_alarms' => $events->filter(fn($e) => $e->incident && str_contains($e->incident->result, 'real'))->count(),
        ];

        return view('admin.reports.print_summary', compact('customer', 'events', 'stats', 'request'));
    }

    /**
     * 3. REPORTE DETALLADO DE INCIDENTE (Imprimible para Autoridades)
     */
    public function detail($incidentId)
    {
        $incident = Incident::with([
            'alarmEvent.account.customer',
            'alarmEvent.siaCode',
            'alarmEvent.account.zones',
            'logs.user'
        ])->findOrFail($incidentId);

        // Historial de los 15 eventos previos de esa misma cuenta para contexto
        $history = AlarmEvent::where('account_number', $incident->alarmEvent->account_number)
            ->where('id', '<', $incident->alarm_event_id)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->with('siaCode')
            ->get();

        return view('admin.reports.print_detail', compact('incident', 'history'));
    }
}