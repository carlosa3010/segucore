@extends('layouts.admin')
@section('title', 'Centro de Alertas')

@section('content')
<div class="bg-slate-900 min-h-screen p-4">
    <h1 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
        <span>🔔</span> Historial de Alertas
    </h1>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
        <table class="w-full text-sm text-left text-slate-400">
            <thead class="bg-slate-950 text-slate-200">
                <tr>
                    <th class="px-4 py-3">Fecha/Hora</th>
                    <th class="px-4 py-3">Vehículo</th>
                    <th class="px-4 py-3">Evento</th>
                    <th class="px-4 py-3">Mensaje</th>
                    <th class="px-4 py-3 text-right">Mapa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($alerts as $alert)
                <tr class="hover:bg-slate-700/50 transition">
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $alert->created_at->format('d/m H:i') }}
                        <span class="text-xs text-slate-500 block">{{ $alert->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <strong class="text-white block">{{ $alert->device->name ?? 'Eliminado' }}</strong>
                        <span class="text-xs">{{ $alert->device->customer->business_name ?? 'N/A' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($alert->type == 'overspeed')
                            <span class="bg-red-900/30 text-red-400 border border-red-900 px-2 py-0.5 rounded text-xs font-bold">EXCESO VELOCIDAD</span>
                        @elseif($alert->type == 'geofence')
                            <span class="bg-yellow-900/30 text-yellow-400 border border-yellow-900 px-2 py-0.5 rounded text-xs font-bold">GEOCERCA</span>
                        @else
                            <span class="bg-slate-700 text-white px-2 py-0.5 rounded text-xs">{{ strtoupper($alert->type) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-white">
                        {{ $alert->message }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if(isset($alert->data['lat']))
                            <a href="https://www.google.com/maps?q={{ $alert->data['lat'] }},{{ $alert->data['lng'] }}" target="_blank" class="text-blue-400 hover:underline text-xs">Ver Ubicación ↗</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center">No hay alertas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $alerts->links() }}</div>
    </div>
</div>
@endsection