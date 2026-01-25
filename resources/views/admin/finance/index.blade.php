@extends('layouts.admin')
@section('title', 'Finanzas y Pagos')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-[#1e293b] p-6 rounded-lg border border-gray-700 text-center">
            <h3 class="text-gray-400 text-sm uppercase mb-2">Tasa de Cambio (BCV/Ref)</h3>
            <p class="text-4xl font-bold text-[#C6F211] mb-4">Bs. {{ number_format($exchangeRate, 2) }}</p>
            
            <form action="{{ route('admin.finance.rate.update') }}" method="POST">
                @csrf
                <div class="flex gap-2">
                    <input type="number" step="0.01" name="exchange_rate" class="form-input text-center" placeholder="Nueva Tasa">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-3 rounded">💾</button>
                </div>
            </form>
        </div>

        <div class="bg-[#1e293b] p-6 rounded-lg border border-gray-700">
            <h3 class="text-white font-bold mb-4">Registrar Pago Rápido</h3>
            <form action="{{ route('admin.finance.payment.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="text-xs text-gray-400">Factura Pendiente</label>
                    <select name="invoice_id" class="form-input">
                        @foreach($pendingInvoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->customer->business_name ?? $inv->customer->last_name }} (${{ $inv->total }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="text-xs text-gray-400">Monto ($)</label>
                    <input type="number" step="0.01" name="amount" class="form-input" required>
                </div>

                <div class="mb-3">
                    <label class="text-xs text-gray-400">Método</label>
                    <select name="method" class="form-input">
                        <option value="zelle">Zelle</option>
                        <option value="transferencia_bs">Transferencia Bs</option>
                        <option value="efectivo">Efectivo ($)</option>
                        <option value="pago_movil">Pago Móvil</option>
                    </select>
                </div>
                
                <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">

                <button type="submit" class="w-full bg-green-600 text-white font-bold py-2 rounded text-sm">Registrar Pago</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-3 bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-700">
            <h2 class="font-bold text-white">Historial de Pagos Recientes</h2>
        </div>
        <table class="w-full text-left text-gray-400 text-sm">
            <thead class="bg-black/20 text-xs uppercase text-gray-300">
                <tr>
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Factura</th>
                    <th class="p-3">Cliente</th>
                    <th class="p-3">Método</th>
                    <th class="p-3 text-right">Monto ($)</th>
                    <th class="p-3 text-right">Ref. Bs (Aprox)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($payments as $payment)
                <tr class="hover:bg-gray-800/50">
                    <td class="p-3">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3 font-mono text-blue-400">{{ $payment->invoice->invoice_number }}</td>
                    <td class="p-3">{{ $payment->invoice->customer->full_name }}</td>
                    <td class="p-3 capitalize">{{ str_replace('_', ' ', $payment->method) }}</td>
                    <td class="p-3 text-right font-bold text-white">${{ number_format($payment->amount, 2) }}</td>
                    <td class="p-3 text-right text-gray-500">Bs {{ number_format($payment->amount * $exchangeRate, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-3">
            {{ $payments->links() }}
        </div>
    </div>

</div>
@endsection