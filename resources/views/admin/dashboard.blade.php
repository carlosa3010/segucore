@extends('layouts.admin')
@section('title', 'Dashboard Principal')

@section('content')
<div class="space-y-8">
    
    <div class="flex justify-between items-end border-b border-slate-800 pb-4">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Centro de Comando</h1>
            <p class="text-slate-400 text-sm mt-1">Visión general del sistema Segusmart</p>
        </div>
        <div class="flex gap-3">
            <div class="text-right hidden sm:block">
                <span class="block text-2xl font-bold text-white">{{ $totalCustomers }}</span>
                <span class="text-xs text-slate-500 uppercase font-bold">Clientes Totales</span>
            </div>
            <div class="text-right hidden sm:block border-l border-slate-700 pl-3">
                <span class="block text-2xl font-bold text-red-500">{{ $openIncidents }}</span>
                <span class="text-xs text-slate-500 uppercase font-bold">Incidentes Activos</span>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="w-2 h-2 bg-purple-500 rounded-full"></span> Seguridad Electrónica (Alarmas)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div class="bg-slate-800 p-5 rounded-lg border border-slate-700 relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10"><span class="text-5xl text-purple-500">📟</span></div>
                <div class="text-sm text-slate-400 mb-1">Cuentas Abonadas</div>
                <div class="text-3xl font-bold text-white">{{ $totalAlarmAccounts }}</div>
                <div class="mt-2 flex items-center gap-2 text-xs">
                    <span class="text-green-400 font-bold">{{ $activePanels }} Activas</span>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-500">{{ $totalAlarmAccounts - $activePanels }} Inactivas</span>
                </div>
            </div>

            <div class="bg-slate-800 p-5 rounded-lg border border-slate-700 relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10"><span class="text-5xl text-blue-500">📶</span></div>
                <div class="text-sm text-slate-400 mb-1">Señales Recibidas (Hoy)</div>
                <div class="text-3xl font-bold text-white">{{ $signalsToday }}</div>
                <div class="mt-2 text-xs text-blue-400">Eventos procesados por el receptor</div>
            </div>

            <div class="bg-slate-800 p-5 rounded-lg border border-slate-700 relative overflow-hidden {{ $criticalSignals > 0 ? 'ring-1 ring-red-500 bg-red-900/10' : '' }}">
                <div class="absolute right-0 top-0 p-4 opacity-10"><span class="text-5xl text-red-500">🔥</span></div>
                <div class="text-sm text-slate-400 mb-1">Eventos Críticos (24h)</div>
                <div class="text-3xl font-bold {{ $criticalSignals > 0 ? 'text-red-400' : 'text-white' }}">{{ $criticalSignals }}</div>
                <div class="mt-2 text-xs text-slate-500">Robo, Pánico, Fuego, Médica</div>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="w-2 h-2 bg-blue-500 rounded-full"></span> Gestión de Flota (GPS)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div class="bg-slate-800 p-5 rounded-lg border border-slate-700 relative overflow-hidden">
                <div class="absolute right-0 top-0 p-4 opacity-10"><span class="text-5xl text-cyan-500">🛰️</span></div>
                <div class="text-sm text-slate-400 mb-1">Total Vehículos</div>
                <div class="text-3xl font-bold text-white">{{ $totalDevices }}</div>
                <div class="mt-2 w-full bg-slate-700 rounded-full h-1.5">
                    @php $percent = $totalDevices > 0 ? ($onlineDevices/$totalDevices)*100 : 0; @endphp
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
                <div class="mt-1 text-[10px] text-slate-400 flex justify-between">
                    <span>{{ $onlineDevices }} Online</span>
                    <span>{{ $offlineDevices }} Offline</span>
                </div>
            </div>

            <a href="{{ route('admin.alerts.index') }}" class="bg-slate-800 p-5 rounded-lg border border-slate-700 relative overflow-hidden hover:bg-slate-750 transition group cursor-pointer">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition"><span class="text-5xl text-yellow-500">⚠️</span></div>
                <div class="text-sm text-slate-400 mb-1">Alertas de Conducción</div>
                <div class="text-3xl font-bold text-white">{{ $gpsAlerts24h }}</div>
                <div class="mt-2 text-xs text-yellow-500 font-medium">Ver historial de excesos →</div>
            </a>

            <div class="grid grid-rows-2 gap-2">
                <a href="{{ route('admin.gps.fleet.index') }}" class="bg-blue-900/20 border border-blue-800/50 hover:bg-blue-900/40 rounded-lg flex items-center justify-center gap-2 text-blue-300 font-bold text-sm transition">
                    🗺️ Mapa en Vivo
                </a>
                <a href="{{ route('admin.gps.devices.create') }}" class="bg-slate-700 border border-slate-600 hover:bg-slate-600 rounded-lg flex items-center justify-center gap-2 text-white font-bold text-sm transition">
                    + Nuevo GPS
                </a>
            </div>
        </div>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700">
        <div class="p-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="font-bold text-white text-sm">🚨 Últimos Incidentes Operativos</h3>
            <a href="{{ route('admin.operations.console') }}" class="text-xs text-blue-400 hover:text-blue-300">Ir a Consola →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="bg-slate-900/50 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Hora</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Atendido Por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($latestIncidents as $inc)
                    <tr>
                        <td class="px-4 py-3">{{ $inc->created_at->format('H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-white">{{ $inc->customer->business_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] bg-slate-700 text-white">{{ $inc->priority ?? 'ALARM' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($inc->status == 'open') <span class="text-red-400 animate-pulse">● Abierto</span>
                            @elseif($inc->status == 'closed') <span class="text-green-400">● Cerrado</span>
                            @else <span class="text-yellow-400">● En Proceso</span> @endif
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $inc->user->name ?? 'Sistema' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center italic text-slate-500">No hay incidentes recientes en la bitácora.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection