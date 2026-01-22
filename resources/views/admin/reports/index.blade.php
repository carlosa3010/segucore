@extends('layouts.admin')

@section('title', 'Reporte de Eventos')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 text-slate-300">
    
    <div class="bg-slate-800 rounded-lg p-6 mb-6 border border-slate-700 shadow-lg">
        <h1 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
            <span>📊</span> Reporte Global de Eventos
        </h1>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase mb-1">Cliente</label>
                <select name="customer_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white">
                    <option value="">-- Todos --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->business_name ?? $c->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->subDays(7)->format('Y-m-d')) }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase mb-1">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1">Estado</label>
                <select name="status" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white">
                    <option value="">Todos</option>
                    <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Procesados</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold text-sm flex-1">
                    🔍 Buscar
                </button>
                @if(request('customer_id'))
                    <button type="submit" formaction="{{ route('admin.reports.summary') }}" formtarget="_blank" class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-2 rounded font-bold text-sm" title="Imprimir PDF Cliente">
                        🖨️ PDF
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-950 text-slate-400 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Fecha/Hora</th>
                    <th class="px-4 py-3">Cuenta</th>
                    <th class="px-4 py-3">Evento</th>
                    <th class="px-4 py-3">Zona</th>
                    <th class="px-4 py-3 text-center">Gestión</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($events as $event)
                    <tr class="hover:bg-slate-700/50 transition">
                        <td class="px-4 py-3 text-slate-300 whitespace-nowrap">
                            {{ $event->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="block font-bold text-white">{{ $event->account_number }}</span>
                            <span class="text-xs text-slate-500">{{ $event->account->branch_name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-2">
                                <span class="font-mono text-yellow-500">{{ $event->event_code }}</span>
                                <span class="text-white">{{ $event->siaCode->description ?? 'Desconocido' }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 font-mono">
                            {{ $event->zone }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($event->incident)
                                <span class="bg-green-900 text-green-300 px-2 py-1 rounded text-xs border border-green-700">
                                    {{ $event->incident->result ? 'CERRADO' : 'EN PROCESO' }}
                                </span>
                            @elseif($event->processed)
                                <span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs">AUTO</span>
                            @else
                                <span class="bg-red-900 text-red-300 px-2 py-1 rounded text-xs animate-pulse">PENDIENTE</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($event->incident)
                                <a href="{{ route('admin.reports.detail', $event->incident->id) }}" target="_blank" class="text-blue-400 hover:text-white font-bold text-xs border border-blue-500/50 px-2 py-1 rounded hover:bg-blue-600 transition">
                                    📄 Ver Informe
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            No se encontraron eventos con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 bg-slate-900 border-t border-slate-700">
            {{ $events->appends(request()->all())->links() }}
        </div>
    </div>
</div>
@endsection