@extends('layouts.admin')
@section('title', 'Dashboard Principal')

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-white">Tablero de Control</h1>
            <p class="text-slate-400 text-sm">Bienvenido, {{ Auth::user()->name }}</p>
        </div>
        <span class="text-xs text-slate-500 bg-slate-800 px-3 py-1 rounded border border-slate-700">
            Última act: {{ now()->format('H:i:s') }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-slate-800 p-4 rounded-lg border border-slate-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition transform group-hover:scale-110">
                <span class="text-6xl">🚨</span>
            </div>
            <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Incidentes Abiertos</h3>
            <p class="text-3xl font-bold text-white mt-1">{{ $openIncidents }}</p>
            <div class="mt-2 text-xs {{ $incidentsToday > 0 ? 'text-red-400' : 'text-slate-500' }}">
                {{ $incidentsToday }} nuevos hoy
            </div>
        </div>

        <div class="bg-slate-800 p-4 rounded-lg border border-slate-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition">
                <span class="text-6xl text-blue-500">📡</span>
            </div>
            <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Flota Conectada</h3>
            <div class="flex items-baseline gap-2 mt-1">
                <p class="text-3xl font-bold text-green-400">{{ $onlineDevices }}</p>
                <span class="text-sm text-slate-500">/ {{ $totalDevices }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                <span class="text-red-400">{{ $offlineDevices }}</span> sin señal
            </div>
        </div>

        <a href="{{ route('admin.alerts.index') }}" class="bg-slate-800 p-4 rounded-lg border border-slate-700 relative overflow-hidden group hover:border-red-500/50 transition cursor-pointer">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition">
                <span class="text-6xl text-red-500">🔔</span>
            </div>
            <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Alertas Recientes</h3>
            <p class="text-3xl font-bold text-white mt-1">{{ $recentAlerts }}</p>
            <div class="mt-2 text-xs font-bold {{ $unreadAlerts > 0 ? 'text-yellow-400 animate-pulse' : 'text-slate-500' }}">
                {{ $unreadAlerts }} sin leer
            </div>
        </a>

        <div class="bg-slate-800 p-4 rounded-lg border border-slate-700 relative overflow-hidden">
            <div class="absolute right-0 top-0 p-4 opacity-10">
                <span class="text-6xl text-purple-500">👥</span>
            </div>
            <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Clientes</h3>
            <p class="text-3xl font-bold text-white mt-1">{{ $totalCustomers }}</p>
            <div class="mt-2 text-xs text-blue-400 hover:underline">
                <a href="{{ route('admin.customers.index') }}">Ver directorio →</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-slate-800 rounded-lg border border-slate-700 p-4">
            <h3 class="text-white font-bold text-sm mb-4 border-b border-slate-700 pb-2">Últimos Eventos de Flota</h3>
            <div class="space-y-3">
                @forelse($latestEvents as $event)
                <div class="flex items-start gap-3 p-2 hover:bg-slate-700/50 rounded transition">
                    <div class="w-8 h-8 rounded bg-slate-900 flex items-center justify-center text-lg shadow-sm border border-slate-600">
                        @if($event->type == 'overspeed') 🏎️ @elseif($event->type == 'geofence') 🌐 @else ⚠️ @endif
                    </div>
                    <div>
                        <p class="text-sm text-white">
                            <span class="font-bold text-blue-400">{{ $event->device->name ?? 'Desconocido' }}</span>: 
                            {{ $event->message }}
                        </p>
                        <p class="text-xs text-slate-500">{{ $event->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-500 italic">No hay eventos recientes.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-slate-800 rounded-lg border border-slate-700 p-4">
            <h3 class="text-white font-bold text-sm mb-4 border-b border-slate-700 pb-2">Acciones Rápidas</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.operations.console') }}" target="_blank" class="flex flex-col items-center justify-center p-4 bg-red-900/20 border border-red-900/50 rounded hover:bg-red-900/40 transition">
                    <span class="text-2xl mb-1">🚨</span>
                    <span class="text-xs font-bold text-red-200">Consola</span>
                </a>
                <a href="{{ route('admin.gps.fleet.index') }}" class="flex flex-col items-center justify-center p-4 bg-blue-900/20 border border-blue-900/50 rounded hover:bg-blue-900/40 transition">
                    <span class="text-2xl mb-1">🛰️</span>
                    <span class="text-xs font-bold text-blue-200">Mapa Flota</span>
                </a>
                <a href="{{ route('admin.customers.create') }}" class="flex flex-col items-center justify-center p-4 bg-slate-700 rounded hover:bg-slate-600 transition">
                    <span class="text-2xl mb-1">👤</span>
                    <span class="text-xs text-slate-200">+ Cliente</span>
                </a>
                <a href="{{ route('admin.gps.devices.create') }}" class="flex flex-col items-center justify-center p-4 bg-slate-700 rounded hover:bg-slate-600 transition">
                    <span class="text-2xl mb-1">🚗</span>
                    <span class="text-xs text-slate-200">+ GPS</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection