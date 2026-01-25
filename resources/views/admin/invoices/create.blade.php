@extends('layouts.admin')
@section('title', 'Generar Factura')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Generar Factura: <span class="text-[#C6F211]">{{ $customer->full_name }}</span></h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="col-span-2 bg-[#1e293b] p-6 rounded-lg border border-gray-700">
            <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2">Desglose de Servicios</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center bg-gray-800 p-3 rounded">
                    <div>
                        <p class="text-white font-bold">{{ $plan->name }} (Base)</p>
                        <p class="text-xs text-gray-400">Mantenimiento mensual</p>
                    </div>
                    <span class="font-mono text-white">${{ number_format($baseCost, 2) }}</span>
                </div>

                <div class="flex justify-between items-center bg-gray-800 p-3 rounded">
                    <div>
                        <p class="text-white font-bold">Dispositivos GPS Activos</p>
                        <p class="text-xs text-gray-400">{{ $gpsCount }} disp. x ${{ $plan->gps_price }}</p>
                    </div>
                    <span class="font-mono text-white">${{ number_format($gpsCost, 2) }}</span>
                </div>

                <div class="flex justify-between items-center bg-gray-800 p-3 rounded">
                    <div>
                        <p class="text-white font-bold">Cuentas de Alarma</p>
                        <p class="text-xs text-gray-400">{{ $alarmCount }} ctas. x ${{ $plan->alarm_price }}</p>
                    </div>
                    <span class="font-mono text-white">${{ number_format($alarmCost, 2) }}</span>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-600 flex justify-between items-center">
                    <span class="text-xl font-bold text-white">TOTAL A PAGAR</span>
                    <span class="text-2xl font-bold text-[#C6F211]">${{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-[#1e293b] p-6 rounded-lg border border-gray-700">
            <form action="{{ route('admin.invoices.store') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="base_cost" value="{{ $baseCost }}">
                <input type="hidden" name="gps_count" value="{{ $gpsCount }}">
                <input type="hidden" name="gps_rate" value="{{ $plan->gps_price }}">
                <input type="hidden" name="alarm_count" value="{{ $alarmCount }}">
                <input type="hidden" name="alarm_rate" value="{{ $plan->alarm_price }}">
                <input type="hidden" name="total" value="{{ $total }}">

                <div class="mb-4">
                    <label class="block text-xs text-gray-400 uppercase mb-1">Nro Factura</label>
                    <input type="text" name="invoice_number" value="{{ $nextInvoice }}" class="form-input bg-gray-900" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-400 uppercase mb-1">Fecha Emisión</label>
                    <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" class="form-input">
                </div>

                <div class="mb-6">
                    <label class="block text-xs text-gray-400 uppercase mb-1">Fecha Vencimiento</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+5 days')) }}" class="form-input">
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded transition">
                    Confirmar y Crear
                </button>
            </form>
        </div>
    </div>
</div>
@endsection