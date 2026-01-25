@extends('layouts.admin')

@section('title', 'Ficha Técnica: ' . $customer->full_name)

@section('content')
    <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg relative overflow-hidden">
        
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
                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded transition border border-gray-600 flex items-center gap-2">
                    <span>✏️</span> Editar
                </a>

                <form action="{{ route('admin.customers.toggle-status', $customer->id) }}" method="POST" onsubmit="return confirm('{{ $customer->is_active ? '¿SUSPENDER CLIENTE? Esto suspenderá también todas sus cuentas de alarma.' : '¿Reactivar Cliente?' }}');">
                    @csrf
                    @if($customer->is_active)
                        <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded font-bold shadow-lg shadow-red-900/50 transition flex items-center gap-2">
                            <span>🚫</span> SUSPENDER
                        </button>
                    @else
                        <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded font-bold shadow-lg shadow-green-900/50 transition flex items-center gap-2">
                            <span>✅</span> REACTIVAR
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <div class="xl:col-span-2 space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-[#C6F211]">📟</span> Monitoreo de Alarmas
                        </h3>
                        <p class="text-xs text-gray-400">Paneles vinculados para facturación</p>
                    </div>
                    <a href="{{ route('admin.accounts.create', ['customer_id' => $customer->id]) }}" class="text-xs bg-[#C6F211] hover:bg-[#a3c90d] text-black px-4 py-2 rounded font-bold transition">
                        + Nueva Cuenta
                    </a>
                </div>

                <div class="p-0">
                    @if($customer->accounts->count() > 0)
                        <table class="w-full text-sm text-left text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3">Abonado (Serial)</th>
                                    <th class="px-6 py-3">Ubicación / Etiqueta</th>
                                    <th class="px-6 py-3">Estado</th>
                                    <th class="px-6 py-3 text-right">Gestión</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($customer->accounts as $acc)
                                    <tr class="hover:bg-gray-800/50 transition">
                                        <td class="px-6 py-4 font-mono font-bold text-white text-base">{{ $acc->account_number }}</td>
                                        <td class="px-6 py-4">
                                            <div class="text-white font-medium">{{ $acc->branch_name ?? 'Principal' }}</div>
                                            <div class="text-xs truncate max-w-[200px]">{{ $acc->installation_address }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold {{ $acc->service_status === 'active' ? 'bg-green-900 text-green-400' : 'bg-red-900 text-red-400' }}">
                                                {{ strtoupper($acc->service_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.accounts.show', $acc->id) }}" class="text-blue-400 hover:text-white text-xs border border-blue-900 px-2 py-1 rounded inline-block">
                                                Gestionar ⚙️
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-8 text-center text-gray-500 border-2 border-dashed border-gray-800 m-4 rounded">No hay cuentas de alarma.</div>
                    @endif
                </div>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-yellow-400">🛰️</span> Rastreo GPS
                        </h3>
                    </div>
                    <a href="{{ route('admin.gps.devices.create', ['customer_id' => $customer->id]) }}" class="text-xs bg-yellow-400 hover:bg-yellow-300 text-black px-4 py-2 rounded font-bold transition">
                        + Vincular GPS
                    </a>
                </div>
                <div class="p-0">
                    @if($customer->gpsDevices->count() > 0)
                        <table class="w-full text-sm text-left text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3">Nombre / IMEI</th>
                                    <th class="px-6 py-3">Modelo</th>
                                    <th class="px-6 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($customer->gpsDevices as $gps)
                                    <tr class="hover:bg-gray-800/50 transition">
                                        <td class="px-6 py-4">
                                            <div class="text-white font-bold">{{ $gps->name }}</div>
                                            <div class="text-xs font-mono">{{ $gps->imei }}</div>
                                        </td>
                                        <td class="px-6 py-4">{{ $gps->device_model ?? 'Genérico' }}</td>
                                        <td class="px-6 py-4 text-right">
                                             <a href="{{ route('admin.gps.devices.show', $gps->id) }}" class="text-blue-400 hover:text-white text-xs border border-blue-900 px-2 py-1 rounded inline-block"> Ver 🛰️ </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden shadow-lg">
                <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2"><span>👥</span> Contactos de Emergencia</h3>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($customer->contacts->sortBy('priority') as $contact)
                        <div class="bg-black/20 p-4 rounded border border-gray-700 flex justify-between items-center">
                            <div>
                                <span class="text-[10px] font-bold text-[#C6F211] uppercase tracking-tighter">Prioridad {{ $contact->priority }}</span>
                                <h4 class="text-white font-bold">{{ $contact->name }}</h4>
                                <p class="text-xs text-gray-400">{{ $contact->relationship }} • <span class="text-blue-300 font-mono">{{ $contact->phone }}</span></p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <div class="space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">Plan de Servicio</h3>
                
                @if($customer->service_plan_id)
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xl font-bold text-[#C6F211]">{{ $customer->servicePlan->name }}</span>
                        <span class="text-xs bg-blue-900/30 text-blue-400 px-2 py-1 rounded border border-blue-800">
                            ${{ number_format($customer->servicePlan->price, 2) }} / mes
                        </span>
                    </div>

                    <a href="{{ route('admin.invoices.create', ['customer' => $customer->id]) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded font-bold transition shadow-lg mb-2">
                        Generar Factura Manual
                    </a>
                @else
                    <div class="bg-red-900/20 border border-red-900/50 p-4 rounded text-center mb-4">
                        <p class="text-red-300 text-xs">Sin plan asignado. No se puede facturar.</p>
                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="text-xs font-bold text-white underline">Asignar Plan</a>
                    </div>
                @endif
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg text-center">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">Resumen Financiero</h3>
                <p class="text-[10px] text-gray-500 mb-1 uppercase">Deuda Pendiente</p>
                <p class="text-4xl font-bold text-red-500 font-mono tracking-tight">$0.00</p>
                <a href="{{ route('admin.finance.index', ['customer_id' => $customer->id]) }}" class="block w-full mt-4 bg-gray-800 hover:bg-gray-700 text-gray-300 py-2 rounded text-xs transition border border-gray-600">
                    💳 Gestionar Pagos
                </a>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">Seguridad</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] text-gray-500 uppercase tracking-wider">Palabra Clave</label>
                        <p class="text-lg text-white font-mono tracking-widest">{{ $customer->monitoring_password ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-[10px] text-red-400 uppercase tracking-wider font-bold">Clave Coacción</label>
                        <p class="text-lg text-red-400 font-mono tracking-widest">{{ $customer->duress_password ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection