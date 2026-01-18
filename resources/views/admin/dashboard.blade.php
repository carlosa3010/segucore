@extends('layouts.admin')

@section('title', 'Dashboard General - SeguCore')

@section('content')
    <h1 class="text-2xl font-bold text-white mb-6">Panel de Control</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-[#1e293b] rounded-lg p-6 border border-gray-700 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Alarmas Activas</p>
                    <p class="text-3xl font-bold text-red-500">{{ $stats['active_alarms'] }}</p>
                </div>
                <div class="p-3 bg-red-900/30 rounded-full text-red-500 text-xl">🔥</div>
            </div>
        </div>

        <div class="bg-[#1e293b] rounded-lg p-6 border border-gray-700 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Clientes</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_customers'] }}</p>
                </div>
                <div class="p-3 bg-blue-900/30 rounded-full text-blue-500 text-xl">👥</div>
            </div>
        </div>

        <div class="bg-[#1e293b] rounded-lg p-6 border border-gray-700 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">GPS Activos</p>
                    <p class="text-3xl font-bold text-[#C6F211]">{{ $stats['active_gps'] }}</p>
                </div>
                <div class="p-3 bg-yellow-900/30 rounded-full text-yellow-500 text-xl">🛰️</div>
            </div>
        </div>

        <div class="bg-[#1e293b] rounded-lg p-6 border border-gray-700 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Incidentes Hoy</p>
                    <p class="text-3xl font-bold text-gray-200">{{ $stats['incidents_today'] }}</p>
                </div>
                <div class="p-3 bg-gray-700/50 rounded-full text-gray-400 text-xl">📋</div>
            </div>
        </div>
    </div>

    <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">🚨 Eventos Críticos Recientes (Sin procesar)</h3>
            <a href="{{ route('admin.operations.console') }}" class="text-sm text-[#C6F211] hover:underline">Ir a Consola Operativa &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-400">
                <thead class="text-xs text-gray-200 uppercase bg-gray-800">
                    <tr>
                        <th class="px-6 py-3">Hora</th>
                        <th class="px-6 py-3">Prioridad</th>
                        <th class="px-6 py-3">Evento</th>
                        <th class="px-6 py-3">Cuenta</th>
                        <th class="px-6 py-3">Zona</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCritical as $event)
                        <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                            <td class="px-6 py-4 font-mono text-white">{{ $event->created_at->format('H:i:s') }}</td>
                            <td class="px-6 py-4">
                                @if($event->priority >= 5)
                                    <span class="bg-red-900 text-red-200 text-xs px-2 py-1 rounded font-bold">CRÍTICO</span>
                                @else
                                    <span class="bg-orange-900 text-orange-200 text-xs px-2 py-1 rounded">ALTO</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-white">{{ $event->sia_desc }}</td>
                            <td class="px-6 py-4">{{ $event->account_number }}</td>
                            <td class="px-6 py-4">{{ $event->zone }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No hay eventos críticos pendientes en este momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection