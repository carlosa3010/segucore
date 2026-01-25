<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index()
    {
        // Obtener tasa actual (o 1 si no existe)
        $exchangeRate = Setting::where('key', 'exchange_rate')->value('value') ?? 1;
        
        // Listado de últimos pagos
        $payments = Payment::with(['invoice.customer'])->latest()->paginate(20);
        
        // Facturas pendientes para el modal de pago manual
        $pendingInvoices = Invoice::with('customer')->where('status', 'unpaid')->get();

        return view('admin.finance.index', compact('exchangeRate', 'payments', 'pendingInvoices'));
    }

    public function updateRate(Request $request)
    {
        $request->validate(['exchange_rate' => 'required|numeric|min:0']);
        
        Setting::updateOrCreate(
            ['key' => 'exchange_rate'],
            ['value' => $request->exchange_rate]
        );

        return back()->with('success', 'Tasa de cambio actualizada correctamente.');
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'required|string'
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        
        // Registrar pago
        $payment = $invoice->payments()->create($request->all());

        // Verificar si la factura está saldada
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        }

        return back()->with('success', 'Pago registrado exitosamente.');
    }
}