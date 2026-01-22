@extends('layouts.admin')

@section('title', 'Reporte de Eventos')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 text-slate-300">
    
    <div class="bg-slate-800 rounded-lg p-6 mb-6 border border-slate-700 shadow-lg">
        <h1 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
            <span>📊</span> Reporte Global e Inteligencia
        </h1>

        <form method="GET" action="{{ route('admin.reports.index') }}" id="reportForm" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 items-end">
            
            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Cliente</label>
                <select name="customer_id" onchange="document.getElementById('reportForm').submit()" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500 focus:outline-none">
                    <option value="">-- Todos los Clientes --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->business_name ?? $c->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Cuenta / Abonado</label>
                <select name="account_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500 focus:outline-none" {{ !request('customer_id') ? 'disabled' : '' }}>
                    <option value="">-- Todas las Cuentas --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->account_number }} - {{ $acc->branch_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->subDays(7)->format('Y-m-d')) }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Evento SIA</label>
                <select name="sia_code" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white">
                    <option value="">-- Todos --</option>
                    @foreach($siaCodes as $code)
                        <option value="{{ $code->code }}" {{ request('sia_code') == $code->code ? 'selected' : '' }}>{{ $code->code }} - {{ $code->description }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase mb-1 text-slate-400">Estado</label>
                <select name="status" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-sm text-white">
                    <option value="">-- Todos --</option>
                    <option value="incident" {{ request('status') == 'incident' ? 'selected' : '' }}>Incidente Real</option>
                    <option value="auto" {{ request('status') == 'auto' ? 'selected' : '' }}>Automático</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>

            <div class="md:col-span-4 lg:col-span-6 flex gap-2 justify-end border-t border-slate-700 pt-4 mt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold text-sm shadow-lg">
                    🔍 Filtrar
                </button>
                
                @if(request('customer_id'))
                    <div class="flex bg-slate-700 rounded overflow-hidden ml-4">
                        <button type="submit" formaction="{{ route('admin.reports.list') }}" formtarget="_blank" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 font-bold text-sm border-r border-gray-500">
                            📄 Lista PDF
                        </button>
                        <button type="submit" formaction="{{ route('admin.reports.summary') }}" formtarget="_blank" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 font-bold text-sm">
                            📊 Gráfico PDF
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden shadow-xl">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-950 text-slate-400 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Cuenta</th>
                    <th class="px-4 py-3">Evento</th>
                    <th class="px-4 py-3">Zona</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right">Ver</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($events as $event)
                    <tr class="hover:bg-slate-700/50 transition">
                        <td class="px-4 py-3 text-slate-300 font-mono text-xs">{{ $event->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-white">{{ $event->account_number }}</span>
                            <span class="text-xs text-slate-500 block">{{ $event->account->branch_name ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-yellow-500 font-bold font-mono mr-1">{{ $event->event_code }}</span>
                            <span class="text-white text-xs">{{ Str::limit($event->siaCode->description ?? '', 30) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ $event->zone }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($event->incident) <span class="text-green-400 text-xs border border-green-800 bg-green-900/30 px-1 rounded">INCIDENTE</span>
                            @elseif($event->processed) <span class="text-slate-500 text-xs">AUTO</span>
                            @else <span class="text-red-400 text-xs animate-pulse">PENDIENTE</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($event->incident)
                                <a href="{{ route('admin.reports.detail', $event->incident->id) }}" target="_blank" class="text-blue-400 hover:text-white">👁️</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-center text-slate-500">Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 bg-slate-900 border-t border-slate-700">{{ $events->appends(request()->all())->links() }}</div>
    </div>
</div>
@endsection