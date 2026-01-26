<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Importante para el PDF

class InvoiceController extends Controller
{
    /**
     * Listado de facturas.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('customer')->orderBy('issue_date', 'desc');
        
        // Filtrar por cliente si se recibe el parámetro
        if($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        
        $invoices = $query->paginate(20);
        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Paso 1: Pre-visualizar factura (Calcular montos).
     * Se espera ?customer_id=X en la URL.
     */
    public function create(Request $request)
    {
        // Validar que venga el ID
        if (!$request->has('customer_id')) {
            return back()->with('error', 'No se especificó un cliente para facturar.');
        }

        $customer = Customer::with(['servicePlan', 'gpsDevices', 'accounts'])
            ->findOrFail($request->customer_id);
        
        if (!$customer->servicePlan) {
            return back()->with('error', 'El cliente no tiene un plan de facturación asignado. Edite el cliente primero.');
        }

        // Lógica de Cálculo basada en el Plan
        $plan = $customer->servicePlan;
        
        // Contar dispositivos activos (ajusta la lógica de 'suspended' según tu necesidad)
        $gpsCount = $customer->gpsDevices->where('is_active', true)->count(); 
        $alarmCount = $customer->accounts->where('service_status', 'active')->count();

        $baseCost = $plan->price;
        $gpsCost = $gpsCount * $plan->gps_price;
        $alarmCost = $alarmCount * $plan->alarm_price;
        
        $total = $baseCost + $gpsCost + $alarmCost;

        // Generar número de factura sugerido (con relleno de ceros)
        $nextInvoice = 'INV-' . str_pad(Invoice::max('id') + 1, 6, '0', STR_PAD_LEFT);

        return view('admin.invoices.create', compact(
            'customer', 
            'plan', 
            'gpsCount', 
            'alarmCount', 
            'baseCost', 
            'gpsCost', 
            'alarmCost', 
            'total', 
            'nextInvoice'
        ));
    }

    /**
     * Guardar la factura en BD.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'total' => 'required|numeric'
        ]);

        // Guardamos los detalles del cálculo en JSON para referencia futura
        $details = [
            'base_price' => $request->input('base_cost'),
            'gps_qty' => $request->input('gps_count'),
            'gps_rate' => $request->input('gps_rate'),
            'alarm_qty' => $request->input('alarm_count'),
            'alarm_rate' => $request->input('alarm_rate'),
        ];

        Invoice::create($validated + [
            'status' => 'unpaid',
            'details' => $details // Asegúrate de tener la columna 'details' en tu migración
        ]);

        return redirect()->route('admin.customers.show', $request->customer_id)
            ->with('success', 'Factura generada exitosamente.');
    }

    /**
     * Muestra el PDF en el navegador (Stream).
     * Este método soluciona el error de "Route::resource missing show method".
     */
    public function show($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'));
        
        // stream() abre el PDF en una pestaña nueva en lugar de descargarlo
        return $pdf->stream('Factura-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Fuerza la descarga del PDF.
     */
    public function download($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'));
        
        // download() fuerza la bajada del archivo
        return $pdf->download('Factura-' . $invoice->invoice_number . '.pdf');
    }
}