@extends('layouts.admin')

@section('title', 'Ficha Técnica: ' . $customer->full_name)

@section('content')
    <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-start">
            <div class="flex gap-4">
                <div class="h-16 w-16 bg-blue-900 rounded-full flex items-center justify-center text-2xl font-bold text-blue-200 border-2 border-blue-500">
                    {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $customer->full_name }}</h1>
                    <div class="flex items-center gap-3 text-sm text-gray-400 mt-1">
                        <span class="px-2 py-0.5 bg-gray-700 rounded text-white">{{ $customer->national_id }}</span>
                        <span>📍 {{ $customer->city }}</span>
                        <span class="{{ $customer->is_active ? 'text-green-400' : 'text-red-400' }}">
                            ● {{ $customer->is_active ? 'Activo' : 'Suspendido' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded transition">
                    Editar Datos
                </a>
                <button class="bg-red-900/50 hover:bg-red-800 text-red-200 px-4 py-2 rounded border border-red-800 transition">
                    Suspender
                </button>
            </div>
        </div>
        
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-black/30 rounded border border-gray-700">
            <div>
                <span class="text-xs text-gray-500 uppercase block">Teléfono 1</span>
                <span class="text-white font-mono">{{ $customer->phone_1 }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-500 uppercase block">Dirección</span>
                <span class="text-white text-sm truncate" title="{{ $customer->address }}">{{ $customer->address }}</span>
            </div>
            <div class="bg-blue-900/20 p-2 rounded border border-blue-900/50">
                <span class="text-xs text-blue-400 uppercase block font-bold">Palabra Clave</span>
                <span class="text-white font-mono font-bold tracking-wider">{{ $customer->monitoring_password }}</span>
            </div>
            <div class="bg-red-900/20 p-2 rounded border border-red-900/50">
                <span class="text-xs text-red-400 uppercase block font-bold">COACCIÓN</span>
                <span class="text-red-200 font-mono font-bold tracking-wider">{{ $customer->duress_password ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-[#C6F211]">📟</span> Cuentas de Alarma
                    </h3>
                    <button class="text-xs bg-[#C6F211] text-black px-3 py-1 rounded font-bold hover:opacity-80">
                        + Agregar Cuenta
                    </button>
                </div>

                @if($customer->accounts && $customer->accounts->count() > 0)
                    <div class="space-y-3">
                        @foreach($customer->accounts as $acc)
                            <div class="flex justify-between items-center p-3 bg-black/40 rounded border border-gray-700 hover:border-gray-500 transition cursor-pointer">
                                <div>
                                    <span class="text-xl font-mono text-white font-bold">{{ $acc->account_number }}</span>
                                    <p class="text-xs text-gray-400">{{ $acc->notes ?? 'Panel Principal' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="block text-xs text-gray-500">Último evento</span>
                                    <span class="text-sm text-green-400">Hace 5 min</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 text-sm border-2 border-dashed border-gray-700 rounded">
                        No tiene paneles asociados.
                    </div>
                @endif
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="text-yellow-400">🛰️</span> Dispositivos GPS
                    </h3>
                    <button class="text-xs bg-yellow-400 text-black px-3 py-1 rounded font-bold hover:opacity-80">
                        + Vincular GPS
                    </button>
                </div>
                
                @if($customer->gpsDevices && $customer->gpsDevices->count() > 0)
                    <div class="p-3 bg-black/40 rounded border border-gray-700">
                        <span class="text-white">Toyota Fortuner - AB123CD</span>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 text-sm border-2 border-dashed border-gray-700 rounded">
                        Sin dispositivos GPS.
                    </div>
                @endif
            </div>

        </div>

        <div class="space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Lista de Llamadas</h3>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 bg-gray-800/50 rounded">
                        <div>
                            <span class="text-xs bg-blue-600 text-white px-1.5 rounded mr-2">1</span>
                            <span class="text-white text-sm">{{ $customer->first_name }} (Titular)</span>
                        </div>
                        <span class="text-xs font-mono text-gray-400">{{ $customer->phone_1 }}</span>
                    </div>

                    @foreach($customer->contacts as $index => $contact)
                        <div class="flex items-center justify-between p-2 bg-gray-800/50 rounded">
                            <div>
                                <span class="text-xs bg-gray-600 text-white px-1.5 rounded mr-2">{{ $index + 2 }}</span>
                                <span class="text-white text-sm">{{ $contact->name }} ({{ $contact->relationship }})</span>
                            </div>
                            <span class="text-xs font-mono text-gray-400">{{ $contact->phone }}</span>
                        </div>
                    @endforeach
                    
                    <button class="w-full mt-3 text-xs text-blue-400 hover:text-white border border-blue-900/50 py-1 rounded">
                        + Agregar Contacto
                    </button>
                </div>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Estado de Cuenta</h3>
                <div class="text-center">
                    <p class="text-gray-500 text-xs mb-1">Saldo Pendiente</p>
                    <p class="text-3xl font-bold text-green-400">$0.00</p>
                    <button class="mt-4 w-full bg-gray-700 hover:bg-gray-600 text-white py-2 rounded text-sm transition">
                        Ver Facturación
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection