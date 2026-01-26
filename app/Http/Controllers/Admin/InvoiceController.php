<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer')->orderBy('issue_date', 'desc');
        if($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        $invoices = $query->paginate(20);
        return view('admin.invoices.index', compact('invoices'));
    }

    // Paso 1: Pre-visualizar factura (Calcular montos)
    public function create(Request $request)
    {
        $customer = Customer::with(['servicePlan', 'gpsDevices', 'accounts'])->findOrFail($request->customer_id);
        
        if (!$customer->servicePlan) {
            return back()->with('error', 'El cliente no tiene un plan de facturación asignado. Edite el cliente primero.');
        }

        // Lógica de Cálculo
        $plan = $customer->servicePlan;
        $gpsCount = $customer->gpsDevices->where('subscription_status', '!=', 'suspended')->count(); // O usar is_active según tu BD
        $alarmCount = $customer->accounts->where('service_status', 'active')->count();

        $baseCost = $plan->price;
        $gpsCost = $gpsCount * $plan->gps_price;
        $alarmCost = $alarmCount * $plan->alarm_price;
        
        $total = $baseCost + $gpsCost + $alarmCost;

        // Generar número de factura sugerido
        $nextInvoice = 'INV-' . str_pad(Invoice::max('id') + 1, 6, '0', STR_PAD_LEFT);

        return view('admin.invoices.create', compact('customer', 'plan', 'gpsCount', 'alarmCount', 'baseCost', 'gpsCost', 'alarmCost', 'total', 'nextInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required',
            'invoice_number' => 'required|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'total' => 'required|numeric'
        ]);

        // Guardamos los detalles del cálculo para referencia futura
        // (Por si cambia el precio del plan mañana, esta factura queda histórica)
        $details = [
            'base_price' => $request->input('base_cost'),
            'gps_qty' => $request->input('gps_count'),
            'gps_rate' => $request->input('gps_rate'),
            'alarm_qty' => $request->input('alarm_count'),
            'alarm_rate' => $request->input('alarm_rate'),
        ];

        Invoice::create($validated + [
            'status' => 'unpaid',
            'details' => $details
        ]);

        return redirect()->route('admin.customers.show', $request->customer_id)
            ->with('success', 'Factura generada exitosamente.');
    }
// Método para descargar el PDF
public function download(Invoice $invoice)
{
    $invoice->load('customer');
    // Usando la librería dompdf (debes tenerla instalada: composer require barryvdh/laravel-dompdf)
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.pdf', compact('invoice'));
    
    return $pdf->download('Factura-' . $invoice->invoice_number . '.pdf');
}

    }