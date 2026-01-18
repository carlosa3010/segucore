@extends('layouts.admin')

@section('title', 'Ficha Técnica: ' . $customer->full_name)

@section('content')
    <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-start">
            <div class="flex gap-4">
                @php
                    $initials = 'SC';
                    if($customer->type === 'company') {
                        $initials = strtoupper(substr($customer->business_name, 0, 2));
                    } else {
                        $initials = strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1));
                    }
                @endphp
                
                <div class="h-16 w-16 bg-blue-900 rounded-full flex items-center justify-center text-xl font-bold text-blue-200 border-2 border-blue-500 shadow-md shrink-0">
                    {{ $initials }}
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-white tracking-wide flex items-center gap-2">
                        {{ $customer->full_name }}
                        @if($customer->type === 'company')
                            <span class="text-xs bg-blue-900 text-blue-200 px-2 py-1 rounded border border-blue-700 font-mono align-middle">EMPRESA</span>
                        @endif
                    </h1>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-400 mt-1">
                        <span class="px-2 py-0.5 bg-gray-700 rounded text-white font-mono border border-gray-600">
                            {{ $customer->national_id }}
                        </span>
                        <span>📍 {{ $customer->city }}</span>
                        <span class="flex items-center gap-1 font-bold {{ $customer->is_active ? 'text-green-400' : 'text-red-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $customer->is_active ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span> 
                            {{ $customer->is_active ? 'Activo' : 'Suspendido' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded transition shadow-sm border border-gray-600">
                    Editar Datos
                </a>
                <button class="bg-red-900/40 hover:bg-red-800 text-red-300 px-4 py-2 rounded border border-red-800/50 transition">
                    Suspender
                </button>
            </div>
        </div>
        
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-[#0f172a]/80 rounded border border-gray-700">
            <div class="p-2">
                <span class="text-[10px] text-gray-500 uppercase tracking-widest block mb-1">Teléfono Principal</span>
                <span class="text-white font-mono text-lg">{{ $customer->phone_1 }}</span>
            </div>
            <div class="p-2 border-l border-gray-700 pl-4">
                <span class="text-[10px] text-gray-500 uppercase tracking-widest block mb-1">Dirección Fiscal / Monitoreo</span>
                <span class="text-white text-sm block leading-snug" title="{{ $customer->address_billing }}">
                    {{ $customer->address_billing }} </span>
            </div>
            <div class="bg-blue-900/20 p-2 pl-4 rounded border-l-4 border-blue-500">
                <span class="text-[10px] text-blue-400 uppercase tracking-widest block font-bold mb-1">Palabra Clave</span>
                <span class="text-white font-mono font-bold tracking-wider text-lg">{{ $customer->monitoring_password ?? '---' }}</span>
            </div>
            <div class="bg-red-900/20 p-2 pl-4 rounded border-l-4 border-red-500">
                <span class="text-[10px] text-red-400 uppercase tracking-widest block font-bold mb-1">CLAVE DE COACCIÓN</span>
                <span class="text-red-200 font-mono font-bold tracking-wider text-lg">
                    {{ $customer->duress_password ?? 'N/A' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-700">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-[#C6F211] text-xl">📟</span> Cuentas de Alarma
                    </h3>
                    <a href="{{ route('admin.accounts.create', ['customer_id' => $customer->id]) }}" 
                       class="text-xs bg-[#C6F211] hover:bg-[#a3c90d] text-black px-3 py-1.5 rounded font-bold transition flex items-center gap-1">
                        <span>+</span> Agregar Cuenta
                    </a>
                </div>

                @if($customer->accounts && $customer->accounts->count() > 0)
                    <div class="space-y-3">
                        @foreach($customer->accounts as $acc)
                            <div class="flex justify-between items-center p-4 bg-gray-900/50 rounded border border-gray-700 hover:border-gray-500 transition cursor-pointer group">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl font-mono text-white font-bold group-hover:text-[#C6F211] transition">
                                            {{ $acc->account_number }}
                                        </span>
                                        <span class="bg-gray-800 text-xs px-2 py-0.5 rounded text-gray-400 border border-gray-700">
                                            SIA-DCS
                                        </span>
                                        @if($acc->branch_name)
                                            <span class="text-sm text-gray-300 font-bold border-l border-gray-600 pl-3">
                                                {{ $acc->branch_name }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1 flex gap-2">
                                        <span>📍 {{ $acc->installation_address ?? 'Misma que cliente' }}</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[10px] uppercase text-gray-500 tracking-wider">Estado</span>
                                    @if($acc->service_status === 'active')
                                        <span class="text-sm text-green-400 font-bold">● ONLINE</span>
                                    @else
                                        <span class="text-sm text-red-400 font-bold">● OFFLINE</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 text-sm border-2 border-dashed border-gray-700 rounded bg-gray-900/30">
                        No tiene paneles de alarma asociados.
                    </div>
                @endif
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-700">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-yellow-400 text-xl">🛰️</span> Dispositivos GPS
                    </h3>
                    <button class="text-xs bg-yellow-400 hover:bg-yellow-300 text-black px-3 py-1.5 rounded font-bold transition flex items-center gap-1">
                        <span>+</span> Vincular GPS
                    </button>
                </div>
                
                @if($customer->gpsDevices && $customer->gpsDevices->count() > 0)
                    <div class="p-3 bg-gray-900/50 rounded border border-gray-700">
                        <span class="text-white">Toyota Fortuner - AB123CD</span>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 text-sm border-2 border-dashed border-gray-700 rounded bg-gray-900/30">
                        Sin dispositivos GPS vinculados.
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">
                    Lista de Llamadas (Prioridad)
                </h3>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2.5 bg-gray-800/40 rounded border border-transparent hover:border-gray-600 transition">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 flex items-center justify-center text-[10px] font-bold bg-blue-600 text-white rounded-full">1</span>
                            <div>
                                <span class="text-white text-sm block leading-none">
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                </span>
                                <span class="text-[10px] text-gray-500">
                                    {{ $customer->type === 'company' ? 'Representante Legal' : 'Titular' }}
                                </span>
                            </div>
                        </div>
                        <a href="tel:{{ $customer->phone_1 }}" class="text-xs font-mono text-gray-300 hover:text-white">{{ $customer->phone_1 }}</a>
                    </div>

                    @foreach($customer->contacts as $index => $contact)
                        <div class="flex items-center justify-between p-2.5 bg-gray-800/40 rounded border border-transparent hover:border-gray-600 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 flex items-center justify-center text-[10px] font-bold bg-gray-600 text-white rounded-full">
                                    {{ $index + 2 }}
                                </span>
                                <div>
                                    <span class="text-white text-sm block leading-none">{{ $contact->name }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $contact->relationship }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-mono text-gray-300">{{ $contact->phone }}</span>
                        </div>
                    @endforeach
                    
                    <button class="w-full mt-4 text-xs text-blue-400 hover:text-white border border-blue-900/50 hover:bg-blue-900/20 py-2 rounded transition border-dashed">
                        + Agregar Contacto Adicional
                    </button>
                </div>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">
                    Finanzas
                </h3>
                <div class="text-center py-2">
                    <p class="text-gray-500 text-xs mb-1 uppercase tracking-wider">Saldo Pendiente</p>
                    <p class="text-4xl font-bold text-green-400 font-mono tracking-tight">$0.00</p>
                    <button class="mt-4 w-full bg-gray-800 hover:bg-gray-700 text-white py-2 rounded text-sm transition border border-gray-600">
                        Ver Historial de Facturas
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection