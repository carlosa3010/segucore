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
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Cliente</label>
                <select name="customer_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500 focus:outline-none">
                    <option value="">-- Todos --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->business_name ?? $c->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->subDays(7)->format('Y-m-d')) }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Tipo de Evento</label>
                <select name="sia_code" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500 focus:outline-none">
                    <option value="">-- Todos --</option>
                    @foreach($siaCodes as $code)
                        <option value="{{ $code->code }}" {{ request('sia_code') == $code->code ? 'selected' : '' }}>
                            {{ $code->code }} - {{ Str::limit($code->description, 15) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Estado</label>
                <select name="status" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500 focus:outline-none">
                    <option value="">Todos</option>
                    <option value="incident" {{ request('status') == 'incident' ? 'selected' : '' }}>Con Incidente</option>
                    <option value="auto" {{ request('status') == 'auto' ? 'selected' : '' }}>Automáticos</option>
                </select>
            </div>

            <div class="md:col-span-4 lg:col-span-6 flex gap-2 justify-end border-t border-slate-700 pt-4 mt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold text-sm shadow-lg shadow-blue-900/50 transition">
                    🔍 Buscar Eventos
                </button>
                
                @if(request('customer_id'))
                    <button type="submit" formaction="{{ route('admin.reports.summary') }}" formtarget="_blank" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded font-bold text-sm shadow-lg shadow-purple-900/50 transition flex items-center gap-2">
                        <span>🖨️</span> Generar Reporte PDF
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-slate-950 text-slate-400 uppercase text-xs border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Fecha/Hora</th>
                        <th class="px-4 py-3">Cuenta</th>
                        <th class="px-4 py-3">Evento</th>
                        <th class="px-4 py-3">Zona</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Informe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-700/50 transition group">
                            <td class="px-4 py-3 text-slate-300 font-mono text-xs">
                                {{ $event->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="block font-bold text-white group-hover:text-blue-400 transition">{{ $event->account_number }}</span>
                                <span class="text-[10px] text-slate-500 uppercase tracking-wider">{{ $event->account->branch_name ?? 'Sin Nombre' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-yellow-500 font-bold bg-yellow-500/10 px-1.5 py-0.5 rounded">{{ $event->event_code }}</span>
                                    <span class="text-white text-xs truncate max-w-[150px]" title="{{ $event->siaCode->description ?? '' }}">
                                        {{ $event->siaCode->description ?? 'Evento desconocido' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-400 font-mono text-xs">
                                {{ $event->zone }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($event->incident)
                                    <span class="bg-green-900/40 text-green-400 px-2 py-1 rounded text-[10px] font-bold border border-green-800 uppercase tracking-wide">
                                        {{ $event->incident->result ? 'CERRADO' : 'EN GESTIÓN' }}
                                    </span>
                                @elseif($event->processed)
                                    <span class="bg-slate-700 text-slate-300 px-2 py-1 rounded text-[10px]">AUTO</span>
                                @else
                                    <span class="bg-red-900/40 text-red-400 px-2 py-1 rounded text-[10px] animate-pulse font-bold border border-red-800">PENDIENTE</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($event->incident)
                                    <a href="{{ route('admin.reports.detail', $event->incident->id) }}" target="_blank" class="text-blue-400 hover:text-white font-bold text-xs border border-blue-500/30 hover:border-blue-500 hover:bg-blue-600 px-2 py-1 rounded transition flex items-center justify-end gap-1 inline-flex">
                                        <span>📄</span> Ver
                                    </a>
                                @else
                                    <span class="text-slate-600 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500 italic">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-2xl opacity-50">📭</span>
                                    <span>No se encontraron eventos con los filtros seleccionados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($events->hasPages())
            <div class="p-4 bg-slate-900 border-t border-slate-700">
                {{ $events->appends(request()->all())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection