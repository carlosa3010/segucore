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
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Aplica filtros comunes para Index y Reportes PDF.
     */
    private function applyFilters($query, Request $request)
    {
        // 1. Cliente
        if ($request->filled('customer_id')) {
            $query->whereHas('account', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        // 2. Cuenta Específica
        if ($request->filled('account_id')) {
            $query->whereHas('account', function($q) use ($request) {
                $q->where('id', $request->account_id);
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

        // 5. Estado (CORREGIDO: Usar has/doesntHave en lugar de incident_id)
        if ($request->filled('status')) {
            if ($request->status == 'incident') {
                $query->has('incident'); // Solo eventos que tienen un incidente asociado
            } elseif ($request->status == 'auto') {
                $query->where('processed', true)->doesntHave('incident'); // Procesados sin incidente
            } elseif ($request->status == 'pending') {
                $query->where('processed', false);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = AlarmEvent::with(['account.customer', 'siaCode', 'incident']);
        $query = $this->applyFilters($query, $request);

        $events = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $customers = Customer::orderBy('first_name')->take(200)->get();
        $siaCodes = SiaCode::orderBy('code')->get();
        
        $accounts = collect();
        if ($request->filled('customer_id')) {
            $accounts = AlarmAccount::where('customer_id', $request->customer_id)->get();
        }

        return view('admin.reports.index', compact('events', 'customers', 'siaCodes', 'accounts'));
    }

    // Reporte TIPO LISTA (Detallado)
    public function printList(Request $request)
    {
        $query = AlarmEvent::with(['account.customer', 'siaCode', 'incident']);
        $query = $this->applyFilters($query, $request);
        
        $events = $query->orderBy('created_at', 'desc')->get();
        $customer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;

        return view('admin.reports.print_list', compact('events', 'customer', 'request'));
    }

    // Reporte TIPO GRÁFICO (Resumen)
    public function printSummary(Request $request)
    {
        $query = AlarmEvent::query();
        $query = $this->applyFilters($query, $request);

        // CORRECCIÓN AQUÍ TAMBIÉN PARA CONTEOS
        $total = $query->count();
        
        // Clonamos query base para sub-conteos
        $incidents = (clone $query)->has('incident')->count();
        $auto = (clone $query)->where('processed', true)->doesntHave('incident')->count();

        // Top 5 Eventos
        $topEvents = (clone $query)
            ->select('event_code', DB::raw('count(*) as total'))
            ->groupBy('event_code')
            ->orderByDesc('total')
            ->take(5)
            ->with('siaCode')
            ->get();

        // Gráfica de Línea (por día)
        $eventsByDay = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $customer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;

        return view('admin.reports.print_graphical', compact('total', 'incidents', 'auto', 'topEvents', 'eventsByDay', 'customer', 'request'));
    }

    public function detail($id)
    {
        $incident = Incident::with(['alarmEvent.account.customer', 'alarmEvent.siaCode', 'logs.user', 'operator'])->findOrFail($id);
        
        $history = AlarmEvent::where('account_number', $incident->alarmEvent->account_number)
            ->where('id', '<', $incident->alarm_event_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->with('siaCode')
            ->get();

        return view('admin.reports.print_detail', compact('incident', 'history'));
    }
}