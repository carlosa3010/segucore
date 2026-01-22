<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmEvent;
use App\Models\AlarmAccount; // Necesario
use App\Models\Customer;
use App\Models\Incident;
use App\Models\SiaCode;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Para estadísticas

class ReportController extends Controller
{
    /**
     * Aplica los filtros comunes a la consulta
     */
    private function applyFilters($query, Request $request)
    {
        // 1. Cliente
        if ($request->filled('customer_id')) {
            $query->whereHas('account', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        // 2. Cuenta Específica (NUEVO)
        if ($request->filled('account_id')) {
            $query->where('account_number', function($q) use ($request) {
                $q->select('account_number')
                  ->from('alarm_accounts')
                  ->where('id', $request->account_id)
                  ->limit(1);
            });
        }

        // 3. Fechas
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 4. Tipo de Evento (SIA)
        if ($request->filled('sia_code')) {
            $query->where('event_code', $request->sia_code);
        }

        // 5. Estado
        if ($request->filled('status')) {
            if ($request->status == 'incident') {
                $query->whereNotNull('incident_id');
            } elseif ($request->status == 'auto') {
                $query->where('processed', true)->whereNull('incident_id');
            } elseif ($request->status == 'pending') {
                $query->where('processed', false);
            }
        }

        return $query;
    }

    /**
     * PANTALLA PRINCIPAL
     */
    public function index(Request $request)
    {
        $query = AlarmEvent::with(['account.customer', 'siaCode', 'incident']);
        $query = $this->applyFilters($query, $request);

        $events = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Datos para filtros
        $customers = Customer::orderBy('first_name')->select('id', 'first_name', 'last_name', 'business_name')->take(200)->get();
        $siaCodes = SiaCode::orderBy('code')->get();
        
        // Cargar cuentas solo si hay cliente seleccionado
        $accounts = collect();
        if ($request->filled('customer_id')) {
            $accounts = AlarmAccount::where('customer_id', $request->customer_id)->get();
        }

        return view('admin.reports.index', compact('events', 'customers', 'siaCodes', 'accounts'));
    }

    /**
     * REPORTE 1: LISTADO PLANO (Respeta todos los filtros)
     */
    public function printList(Request $request)
    {
        $query = AlarmEvent::with(['account.customer', 'siaCode', 'incident']);
        $query = $this->applyFilters($query, $request); // Aplica mismos filtros que el index
        
        $events = $query->orderBy('created_at', 'desc')->get(); // Sin paginación
        $customer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;

        return view('admin.reports.print_list', compact('events', 'customer', 'request'));
    }

    /**
     * REPORTE 2: RESUMEN EJECUTIVO / GRÁFICO (Respeta todos los filtros)
     */
    public function printSummary(Request $request)
    {
        // Consulta base filtrada
        $query = AlarmEvent::query();
        $query = $this->applyFilters($query, $request);

        // Estadísticas Generales
        $total = $query->count();
        $processed = (clone $query)->where('processed', true)->count();
        $incidents = (clone $query)->whereNotNull('incident_id')->count();
        $auto = $processed - $incidents;

        // Top 5 Eventos (Para gráfica)
        $topEvents = (clone $query)
            ->select('event_code', DB::raw('count(*) as total'))
            ->groupBy('event_code')
            ->orderByDesc('total')
            ->take(5)
            ->with('siaCode')
            ->get();

        // Eventos por Día (Para gráfica lineal simple)
        $eventsByDay = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $customer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;

        return view('admin.reports.print_graphical', compact('total', 'incidents', 'auto', 'topEvents', 'eventsByDay', 'customer', 'request'));
    }

    /**
     * REPORTE 3: DETALLE FORENSE (Incidente Individual)
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

        $history = AlarmEvent::where('account_number', $incident->alarmEvent->account_number)
            ->where('id', '<', $incident->alarm_event_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->with('siaCode')
            ->get();

        return view('admin.reports.print_detail', compact('incident', 'history'));
    }
}