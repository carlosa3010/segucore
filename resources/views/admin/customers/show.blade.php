@extends('layouts.admin')

@section('title', 'Ficha Técnica: ' . $customer->full_name)

@section('content')
    <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg relative overflow-hidden">
        {{-- Encabezado del perfil (sin cambios) --}}
        <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none">
            <span class="text-9xl font-bold {{ $customer->is_active ? 'text-green-500' : 'text-red-500' }}">
                {{ $customer->is_active ? 'ACT' : 'SUS' }}
            </span>
        </div>

        <div class="flex justify-between items-start relative z-10">
            <div class="flex gap-5">
                @php
                    $initials = 'SC';
                    if($customer->type === 'company') {
                        $initials = strtoupper(substr($customer->business_name, 0, 2));
                    } else {
                        $initials = strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1));
                    }
                @endphp
                <div class="h-20 w-20 rounded-xl flex items-center justify-center text-3xl font-bold border-2 shadow-2xl
                    {{ $customer->is_active ? 'bg-blue-900 text-blue-200 border-blue-500' : 'bg-red-900 text-red-200 border-red-500 grayscale' }}">
                    {{ $initials }}
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-white tracking-wide flex items-center gap-3">
                        {{ $customer->full_name }}
                        @if($customer->type === 'company')
                            <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded border border-gray-600 uppercase tracking-widest">Empresa</span>
                        @endif
                    </h1>
                    
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400 mt-2">
                        <div class="flex items-center gap-2 bg-black/30 px-3 py-1 rounded border border-gray-700">
                            <span>🆔 {{ $customer->national_id }}</span>
                        </div>
                        <div class="flex items-center gap-2 bg-black/30 px-3 py-1 rounded border border-gray-700">
                            <span>📧 {{ $customer->email }}</span>
                        </div>
                        <div class="flex items-center gap-2 bg-black/30 px-3 py-1 rounded border border-gray-700">
                            <span>📍 {{ $customer->city }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded transition border border-gray-600 flex items-center gap-2 text-sm">
                    <span>✏️</span> Editar Perfil
                </a>

                <form action="{{ route('admin.customers.toggle-status', $customer->id) }}" method="POST" onsubmit="return confirm('{{ $customer->is_active ? '¿SUSPENDER CLIENTE? Esto suspenderá también sus cuentas de alarma.' : '¿Reactivar Cliente?' }}');">
                    @csrf
                    @if($customer->is_active)
                        <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded font-bold shadow-lg shadow-red-900/50 transition flex items-center gap-2 text-sm uppercase">
                            <span>🚫</span> Suspender
                        </button>
                    @else
                        <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded font-bold shadow-lg shadow-green-900/50 transition flex items-center gap-2 text-sm uppercase">
                            <span>✅</span> Reactivar
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <div class="xl:col-span-2 space-y-6">
            
            {{-- SECCIÓN ALARMAS --}}
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-[#C6F211]">📟</span> Monitoreo de Alarmas
                    </h3>
                    <a href="{{ route('admin.accounts.create', ['customer_id' => $customer->id]) }}" class="text-xs bg-[#C6F211] hover:bg-[#a3c90d] text-black px-4 py-2 rounded font-bold transition">
                        + Nueva Cuenta
                    </a>
                </div>
                <div class="p-0 overflow-x-auto">
                    @if($customer->accounts->count() > 0)
                        <table class="w-full text-sm text-left text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3">Abonado</th>
                                    <th class="px-6 py-3">Ubicación</th>
                                    <th class="px-6 py-3 text-right">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($customer->accounts as $acc)
                                    <tr class="hover:bg-gray-800/50 transition cursor-pointer" onclick="window.location='{{ route('admin.accounts.show', $acc->id) }}'">
                                        <td class="px-6 py-4 font-mono font-bold text-white">{{ $acc->account_number }}</td>
                                        <td class="px-6 py-4">
                                            <div class="text-white">{{ $acc->branch_name ?? 'Principal' }}</div>
                                            <div class="text-[10px] truncate max-w-[200px]">{{ $acc->installation_address }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $acc->service_status === 'active' ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-red-900/30 text-red-400 border border-red-800' }}">
                                                {{ strtoupper($acc->service_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-8 text-center text-gray-500 italic">No hay cuentas de alarma registradas.</div>
                    @endif
                </div>
            </div>

            {{-- SECCIÓN GPS (RECUPERADA) --}}
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-blue-400">🛰️</span> Rastreo GPS
                    </h3>
                    {{-- Asumiendo que la ruta create de GPS funciona similar a accounts --}}
                    <a href="{{ route('admin.gps.devices.create', ['customer_id' => $customer->id]) }}" class="text-xs bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold transition">
                        + Nuevo Dispositivo
                    </a>
                </div>
                <div class="p-0 overflow-x-auto">
                    @if($customer->gpsDevices->count() > 0)
                        <table class="w-full text-sm text-left text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3">Dispositivo</th>
                                    <th class="px-6 py-3">IMEI / Placa</th>
                                    <th class="px-6 py-3 text-right">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($customer->gpsDevices as $device)
                                    <tr class="hover:bg-gray-800/50 transition cursor-pointer" onclick="window.location='{{ route('admin.gps.devices.show', $device->id) }}'">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-white">{{ $device->name }}</div>
                                            <div class="text-[10px]">{{ $device->model }}</div>
                                        </td>
                                        <td class="px-6 py-4 font-mono">
                                            <div class="text-white">{{ $device->imei }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $device->plate_number ?? 'S/P' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $device->is_active ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-red-900/30 text-red-400 border border-red-800' }}">
                                                {{ $device->is_active ? 'ONLINE' : 'OFFLINE' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-8 text-center text-gray-500 italic">No hay dispositivos GPS registrados.</div>
                    @endif
                </div>
            </div>

            {{-- SECCIÓN FACTURACIÓN --}}
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-gray-400">🧾</span> Historial de Facturación
                    </h3>
                </div>
                <div class="p-0">
                    @if($customer->invoices && $customer->invoices->count() > 0)
                        <table class="w-full text-sm text-left text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3">Número</th>
                                    <th class="px-6 py-3">Fecha</th>
                                    <th class="px-6 py-3 text-right">Total</th>
                                    <th class="px-6 py-3 text-center">Estado</th>
                                    <th class="px-6 py-3 text-right">PDF</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($customer->invoices->sortByDesc('issue_date') as $invoice)
                                    <tr class="hover:bg-gray-800/50 transition">
                                        <td class="px-6 py-4 font-mono text-blue-400">{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-4 text-xs">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-white">${{ number_format($invoice->total, 2) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-[10px] px-2 py-0.5 rounded font-bold {{ $invoice->status == 'paid' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' }}">
                                                {{ strtoupper($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            {{-- Aquí se debe definir la ruta admin.invoices.download en web.php --}}
                                            <a href="#" class="bg-gray-700 hover:bg-red-600 text-white p-1.5 rounded transition inline-block" title="Descargar PDF">
                                                📄
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-8 text-center text-gray-500 italic">No se han generado facturas para este cliente.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">
                    Plan de Servicio
                </h3>
                @if($customer->servicePlan)
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xl font-bold text-[#C6F211]">{{ $customer->servicePlan->name }}</span>
                        <span class="text-xs bg-blue-900/30 text-blue-400 px-2 py-1 rounded border border-blue-800">
                            ${{ number_format($customer->servicePlan->price, 2) }} / mes
                        </span>
                    </div>
                    <div class="space-y-2 text-xs text-gray-400 mb-6">
                        <div class="flex justify-between">
                            <span>Tasa GPS:</span>
                            <span class="text-white font-mono">${{ number_format($customer->servicePlan->gps_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tasa Alarma:</span>
                            <span class="text-white font-mono">${{ number_format($customer->servicePlan->alarm_price, 2) }}</span>
                        </div>
                    </div>

                    {{-- CORREGIDO: Usamos customer_id para coincidir con el controlador y la nueva ruta --}}
                    <a href="{{ route('admin.invoices.create', ['customer_id' => $customer->id]) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded font-bold transition shadow-lg mb-2">
                        Generar Factura Manual
                    </a>
                @else
                    <div class="bg-red-900/20 border border-red-900/50 p-4 rounded text-center mb-4">
                        <p class="text-red-300 text-xs mb-3">Este cliente no tiene un plan asignado. No se puede facturar.</p>
                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="text-xs font-bold text-white underline">Asignar Plan Ahora</a>
                    </div>
                    <button class="w-full bg-gray-700 text-gray-500 py-2.5 rounded font-bold cursor-not-allowed" disabled>
                        Generar Factura Manual
                    </button>
                @endif
            </div>

            {{-- ... resto de sidebar (Resumen Activos, Administración) ... --}}
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-700 pb-2">
                    Resumen de Activos
                </h3>
                
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-400 text-sm">Cuentas Alarma</span>
                    <span class="text-white font-mono font-bold">{{ $customer->accounts->count() }}</span>
                </div>
                <div class="flex justify-between items-center mb-6">
                    <span class="text-gray-400 text-sm">Dispositivos GPS</span>
                    <span class="text-white font-mono font-bold">{{ $customer->gpsDevices->count() }}</span>
                </div>

                <div class="bg-black/30 rounded p-4 text-center border border-gray-700 mb-4">
                    <p class="text-gray-500 text-[10px] mb-1 uppercase tracking-tighter">Saldo Pendiente ($)</p>
                    @php
                        $unpaidBalance = $customer->invoices ? $customer->invoices->where('status', 'unpaid')->sum('total') : 0;
                    @endphp
                    <p class="text-4xl font-bold {{ $unpaidBalance > 0 ? 'text-red-500' : 'text-green-400' }} font-mono tracking-tight">
                        ${{ number_format($unpaidBalance, 2) }}
                    </p>
                </div>

                <a href="{{ route('admin.finance.index', ['customer_id' => $customer->id]) }}" class="block w-full text-center bg-gray-800 hover:bg-gray-700 text-gray-300 py-2 rounded text-xs transition border border-gray-600">
                    💳 Gestionar Pagos
                </a>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">
                    Administración
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] text-gray-500 uppercase">Teléfono Principal</label>
                        <p class="text-sm text-white font-mono">{{ $customer->phone_1 }}</p>
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 uppercase">Ciudad</label>
                        <p class="text-sm text-white">{{ $customer->city }}</p>
                    </div>
                    @if($customer->monitoring_password)
                    <div class="mt-4 p-3 bg-red-900/10 border border-red-900/30 rounded">
                        <label class="block text-[10px] text-red-400 uppercase font-bold tracking-widest">Palabra Clave</label>
                        <p class="text-lg text-white font-mono tracking-widest">{{ $customer->monitoring_password }}</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection