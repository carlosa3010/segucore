@extends('layouts.admin')
@section('title', 'Dispositivos GPS')

@section('content')
<div class="bg-slate-900 min-h-screen p-4">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span>🛰️</span> Inventario de GPS
            </h1>
            <p class="text-slate-400 text-sm">Gestión de unidades y estado de conexión en tiempo real</p>
        </div>
        
        <div class="flex gap-2 w-full md:w-auto">
            <form method="GET" class="flex flex-1 md:flex-none">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar IMEI, Placa o Nombre..." class="bg-slate-800 border-slate-700 text-slate-300 text-sm rounded-l-lg focus:ring-blue-500 focus:border-blue-500 block w-full md:w-64 p-2.5">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white p-2.5 rounded-r-lg border border-l-0 border-slate-700">
                    🔍
                </button>
            </form>

            <a href="{{ route('admin.gps.devices.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold text-sm transition shadow-lg shadow-blue-900/50 flex items-center gap-2 whitespace-nowrap">
                <span>+</span> Nuevo
            </a>
        </div>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="bg-slate-950 text-xs uppercase text-slate-300">
                    <tr>
                        <th class="px-4 py-3">Dispositivo</th>
                        <th class="px-4 py-3">Identificador (IMEI)</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Estado Conexión</th>
                        <th class="px-4 py-3">Último Reporte</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($devices as $dev)
                        @php 
                            // Buscamos los datos de Traccar en el array que pasamos desde el controlador
                            $traccar = $traccarData[$dev->imei] ?? null;
                            $isOnline = $traccar && $traccar->status == 'online'; 
                            $lastUpdate = $traccar && $traccar->lastupdate ? \Carbon\Carbon::parse($traccar->lastupdate) : null;
                        @endphp
                        <tr class="hover:bg-slate-700/50 transition group">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded bg-slate-900 border border-slate-700 text-xl hidden sm:block">
                                        @if($dev->vehicle_type == 'car') 🚗
                                        @elseif($dev->vehicle_type == 'truck') 🚚
                                        @elseif($dev->vehicle_type == 'motorcycle') 🏍️
                                        @else 📦 @endif
                                    </div>
                                    <div>
                                        <span class="block text-white font-bold group-hover:text-blue-400 transition">
                                            {{ $dev->name }}
                                        </span>
                                        <span class="text-xs text-slate-500 flex items-center gap-1">
                                            <span>{{ $dev->device_model }}</span>
                                            @if($dev->plate_number)
                                                <span class="bg-slate-700 px-1 rounded text-[10px] text-slate-300">{{ $dev->plate_number }}</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-yellow-500 tracking-wider">
                                {{ $dev->imei }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-white text-sm">{{ $dev->customer->business_name ?? $dev->customer->full_name }}</div>
                                <div class="text-xs text-slate-600">{{ $dev->customer->national_id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($traccar)
                                    @if($traccar->status == 'online')
                                        <span class="inline-flex items-center gap-1.5 text-green-400 bg-green-900/20 px-2.5 py-1 rounded-full text-xs font-bold border border-green-900/50">
                                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> ONLINE
                                        </span>
                                    @elseif($traccar->status == 'offline')
                                        <span class="inline-flex items-center gap-1.5 text-slate-400 bg-slate-700/30 px-2.5 py-1 rounded-full text-xs font-bold border border-slate-600">
                                            <span class="w-2 h-2 rounded-full bg-slate-500"></span> OFFLINE
                                        </span>
                                    @else
                                        <span class="text-yellow-400 text-xs uppercase">{{ $traccar->status }}</span>
                                    @endif
                                @else
                                    <span class="text-red-500 text-xs italic flex items-center gap-1" title="No existe en la DB de Traccar">
                                        ⚠️ Sin Sincronización
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($lastUpdate)
                                    <span class="{{ $lastUpdate->diffInMinutes() < 10 ? 'text-green-300 font-bold' : 'text-slate-500' }}">
                                        {{ $lastUpdate->diffForHumans() }}
                                    </span>
                                    <div class="text-[10px] text-slate-600">{{ $lastUpdate->format('d/m/Y H:i:s') }}</div>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.gps.devices.show', $dev->id) }}" class="p-1.5 rounded hover:bg-slate-700 text-blue-400 hover:text-blue-300 transition" title="Mapa en Vivo">
                                        🗺️
                                    </a>
                                    
                                    <a href="{{ route('admin.gps.devices.history', $dev->id) }}" class="p-1.5 rounded hover:bg-slate-700 text-green-400 hover:text-green-300 transition" title="Historial de Ruta">
                                        📜
                                    </a>

                                    <a href="{{ route('admin.gps.devices.edit', $dev->id) }}" class="p-1.5 rounded hover:bg-slate-700 text-yellow-400 hover:text-yellow-300 transition" title="Editar">
                                        ✏️
                                    </a>
                                    
                                    <form action="{{ route('admin.gps.devices.destroy', $dev->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este dispositivo? Esto lo borrará de Traccar también.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded hover:bg-red-900/50 text-red-400 hover:text-red-300 transition" title="Eliminar">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <span class="text-4xl opacity-50">📡</span>
                                    <span class="text-lg">No hay dispositivos GPS registrados.</span>
                                    <a href="{{ route('admin.gps.devices.create') }}" class="text-blue-400 hover:underline text-sm">Registrar el primero ahora</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($devices->hasPages())
            <div class="p-4 bg-slate-900 border-t border-slate-700">
                {{ $devices->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection