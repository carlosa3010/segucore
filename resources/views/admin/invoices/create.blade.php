@extends('layouts.admin')
@section('title', 'Generar Factura')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg">
        <h2 class="text-xl font-bold text-white mb-6 border-b border-gray-700 pb-2">
            Generar Factura para: <span class="text-blue-400">{{ $customer->full_name }}</span>
        </h2>

        <form action="{{ route('admin.invoices.store') }}" method="POST">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            
            <input type="hidden" name="base_cost" value="{{ $baseCost }}">
            <input type="hidden" name="gps_count" value="{{ $gpsCount }}">
            <input type="hidden" name="gps_rate" value="{{ $plan->gps_price }}">
            <input type="hidden" name="alarm_count" value="{{ $alarmCount }}">
            <input type="hidden" name="alarm_rate" value="{{ $plan->alarm_price }}">

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="text-xs text-gray-500 uppercase">N° Factura</label>
                    <input type="text" name="invoice_number" value="{{ $nextInvoice }}" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-white mt-1">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Emisión</label>
                        <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-white mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Vencimiento</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+5 days')) }}" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-white mt-1">
                    </div>
                </div>
            </div>

            <div class="bg-gray-900/50 rounded border border-gray-700 p-4 mb-6">
                <h3 class="text-sm font-bold text-gray-300 mb-3 uppercase">Detalle del Plan: {{ $plan->name }}</h3>
                
                <table class="w-full text-sm text-gray-400">
                    <thead class="border-b border-gray-700">
                        <tr>
                            <th class="text-left pb-2">Concepto</th>
                            <th class="text-center pb-2">Cantidad</th>
                            <th class="text-right pb-2">Precio Unit.</th>
                            <th class="text-right pb-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr>
                            <td class="py-2">Cuota Base del Plan</td>
                            <td class="text-center">1</td>
                            <td class="text-right">${{ number_format($baseCost, 2) }}</td>
                            <td class="text-right font-medium text-white">${{ number_format($baseCost, 2) }}</td>
                        </tr>
                        @if($gpsCount > 0)
                        <tr>
                            <td class="py-2 text-yellow-400">Servicio de Rastreo GPS</td>
                            <td class="text-center">{{ $gpsCount }}</td>
                            <td class="text-right">${{ number_format($plan->gps_price, 2) }}</td>
                            <td class="text-right font-medium text-white">${{ number_format($gpsCost, 2) }}</td>
                        </tr>
                        @endif
                        @if($alarmCount > 0)
                        <tr>
                            <td class="py-2 text-[#C6F211]">Monitoreo de Alarmas</td>
                            <td class="text-center">{{ $alarmCount }}</td>
                            <td class="text-right">${{ number_format($plan->alarm_price, 2) }}</td>
                            <td class="text-right font-medium text-white">${{ number_format($alarmCost, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot class="border-t border-gray-700">
                        <tr>
                            <td colspan="3" class="pt-4 text-right font-bold text-lg text-white">TOTAL A PAGAR:</td>
                            <td class="pt-4 text-right font-bold text-2xl text-green-400">
                                $<input type="text" name="total" value="{{ $total }}" class="bg-transparent text-right w-24 focus:outline-none" readonly>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ url()->previous() }}" class="px-4 py-2 rounded border border-gray-600 text-gray-300 hover:bg-gray-800">Cancelar</a>
                <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded font-bold shadow-lg shadow-green-900/50">
                    Confirmar y Crear Factura
                </button>
            </div>
        </form>
    </div>
</div>
@endsection