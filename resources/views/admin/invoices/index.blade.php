@extends('layouts.admin')
@section('title', 'Facturación')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">🧾 Facturación</h1>
    {{-- Botón para generar facturas masivas si quisieras implementarlo --}}
</div>

<div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
    <table class="w-full text-left text-gray-400">
        <thead class="bg-black/20 text-xs uppercase font-bold text-gray-300">
            <tr>
                <th class="p-4"># Factura</th>
                <th class="p-4">Cliente</th>
                <th class="p-4">Emisión</th>
                <th class="p-4">Vencimiento</th>
                <th class="p-4">Total</th>
                <th class="p-4">Estado</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @foreach($invoices as $invoice)
            <tr class="hover:bg-gray-800/50 transition">
                <td class="p-4 font-mono text-white">{{ $invoice->invoice_number }}</td>
                <td class="p-4">{{ $invoice->customer->full_name ?? 'Cliente Eliminado' }}</td>
                <td class="p-4">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                <td class="p-4">{{ $invoice->due_date->format('d/m/Y') }}</td>
                <td class="p-4 font-bold text-white">${{ number_format($invoice->total, 2) }}</td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $invoice->status == 'paid' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </td>
                <td class="p-4">
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-blue-400 hover:text-blue-300 text-sm">Ver</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">
        {{ $invoices->links() }}
    </div>
</div>
@endsection