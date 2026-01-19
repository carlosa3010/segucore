@extends('layouts.operations')

@section('title', 'Consola de Monitoreo')

@section('content')
<div class="h-full flex flex-col bg-slate-900 p-2">
    
    <div class="flex justify-between items-end mb-2 px-2 shrink-0">
        <h1 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="w-3 h-3 bg-red-500 rounded-full animate-ping"></span> 
            Cola de Eventos
        </h1>
        
        <div class="flex items-center gap-4">
            <button onclick="document.getElementById('manualEventModal').showModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-1.5 rounded text-xs font-bold uppercase shadow-lg shadow-blue-900/40 transition flex items-center gap-2 border border-blue-500">
                <span>➕</span> Crear Ticket Manual
            </button>
            
            <div class="text-xs text-slate-400">Refresco: <span class="text-green-400">Activo</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-2 h-full overflow-hidden">
        
        <div class="lg:col-span-3 bg-slate-950 border border-slate-800 rounded flex flex-col overflow-hidden">
            <div class="grid grid-cols-12 gap-2 bg-slate-900 px-4 py-2 text-xs font-bold text-slate-400 uppercase border-b border-slate-800 shrink-0">
                <div class="col-span-1">Prioridad</div>
                <div class="col-span-1">Hora</div>
                <div class="col-span-2">Cuenta</div>
                <div class="col-span-4">Evento</div>
                <div class="col-span-3">Cliente / Zona</div>
                <div class="col-span-1 text-right">Acción</div>
            </div>

            <div class="overflow-y-auto flex-1 p-0 scroll-smooth">
                @forelse($pendingEvents as $event)
                    <div class="grid grid-cols-12 gap-2 items-center px-4 py-2 border-b border-slate-800/50 hover:bg-slate-800 transition text-sm group
                        {{ $event->siaCode->priority >= 4 ? 'bg-red-900/10 border-l-2 border-l-red-500' : 'border-l-2 border-l-slate-700' }}">
                        
                        <div class="col-span-1">
                            @if($event->siaCode->priority >= 5) <span class="bg-red-600 text-white px-1.5 py-0.5 rounded text-[10px] font-bold animate-pulse">PÁNICO</span>
                            @elseif($event->siaCode->priority == 4) <span class="bg-orange-600 text-white px-1.5 py-0.5 rounded text-[10px] font-bold">ROBO</span>
                            @else <span class="bg-blue-600 text-white px-1.5 py-0.5 rounded text-[10px]">INFO</span> @endif
                        </div>
                        
                        <div class="col-span-1 font-mono text-slate-300 text-xs">
                            {{ $event->created_at->format('H:i:s') }}
                        </div>
                        
                        <div class="col-span-2 font-mono font-bold text-yellow-500">
                            {{ $event->account_number }}
                        </div>
                        
                        <div class="col-span-4 font-bold text-white truncate" title="{{ $event->siaCode->description }}">
                            {{ $event->event_code }} - {{ $event->siaCode->description }}
                        </div>
                        
                        <div class="col-span-3 text-slate-400 truncate text-xs">
                            {{ $event->account->customer->business_name ?? $event->account->customer->full_name }}
                            <span class="text-slate-600">|</span> Z:{{ $event->zone }}
                        </div>
                        
                        <div class="col-span-1 text-right opacity-80 group-hover:opacity-100">
                            <form action="{{ route('admin.incidents.take', $event->id) }}" method="POST">
                                @csrf
                                <button class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded text-xs font-bold shadow-lg shadow-blue-900/20">
                                    Atender
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-600">
                        <div class="text-4xl mb-2 opacity-20">🛡️</div>
                        <p class="text-sm">Sin eventos pendientes</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded flex flex-col">
            <div class="bg-slate-800 px-3 py-2 text-xs font-bold text-white border-b border-slate-700 flex justify-between shrink-0">
                <span>Mis Casos Activos</span>
                <span class="bg-slate-600 px-1.5 rounded-full">{{ count($myIncidents) }}</span>
            </div>
            <div class="overflow-y-auto flex-1 p-2 space-y-2">
                @foreach($myIncidents as $inc)
                    <div class="bg-slate-950 p-3 rounded border-l-2 {{ $inc->status == 'police_dispatched' ? 'border-red-500' : 'border-yellow-500' }} hover:border-blue-400 transition cursor-pointer relative"
                         onclick="window.location='{{ route('admin.operations.manage', $inc->id) }}'">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-white text-sm">{{ $inc->alarmEvent->account_number }}</span>
                            <span class="text-[10px] text-slate-400">{{ $inc->started_at->format('H:i') }}</span>
                        </div>
                        <div class="text-xs text-slate-300 truncate mb-2">{{ $inc->alarmEvent->siaCode->description ?? 'Evento Manual' }}</div>
                        
                        <div class="flex gap-1">
                            @if($inc->status == 'police_dispatched')
                                <span class="bg-red-900/50 text-red-400 text-[10px] px-1 rounded border border-red-900">👮 Policía</span>
                            @else
                                <span class="bg-yellow-900/50 text-yellow-400 text-[10px] px-1 rounded border border-yellow-900">⏳ Espera</span>
                            @endif
                            <span class="text-[10px] bg-slate-800 px-1 rounded text-slate-400 ml-auto">Retomar &rarr;</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<dialog id="manualEventModal" class="m-auto bg-slate-900 text-white p-0 rounded-lg border border-slate-700 shadow-2xl backdrop:bg-black/90 w-full max-w-md open:animate-fade-in text-left fixed inset-0 z-50">
    <div class="p-4 border-b border-slate-800 bg-slate-950 flex justify-between items-center">
        <h3 class="font-bold text-sm uppercase text-white flex items-center gap-2">
            <span>🚨</span> Generar Incidente Manual
        </h3>
        <button type="button" onclick="document.getElementById('manualEventModal').close()" class="text-slate-500 hover:text-white font-bold px-2">✕</button>
    </div>
    
    <form action="{{ route('admin.incidents.manual') }}" method="POST" class="p-6">
        @csrf
        
        <div class="mb-4">
            <label class="block text-xs text-slate-400 uppercase font-bold mb-2">Cuenta / Cliente</label>
            <select name="account_id" class="w-full bg-slate-800 border border-slate-600 p-2.5 rounded text-sm text-white focus:outline-none focus:border-blue-500" required>
                <option value="" disabled selected>Buscar cuenta...</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">
                        {{ $acc->account_number }} - {{ $acc->branch_name }} 
                        ({{ $acc->customer->business_name ?? $acc->customer->full_name }})
                    </option>
                @endforeach
            </select>
            <p class="text-[10px] text-slate-500 mt-1">Seleccione la cuenta que reporta la emergencia.</p>
        </div>

        <div class="mb-4">
            <label class="block text-xs text-slate-400 uppercase font-bold mb-2">Tipo de Incidente</label>
            <select name="event_code" class="w-full bg-slate-800 border border-slate-600 p-2.5 rounded text-sm text-white focus:outline-none focus:border-blue-500" required>
                <option value="" disabled selected>Seleccione Clasificación...</option>
                @foreach($siaCodes as $code)
                    <option value="{{ $code->code }}">{{ $code->code }} - {{ $code->description }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-xs text-slate-400 uppercase font-bold mb-2">Descripción del Reporte</label>
            <textarea name="note" class="w-full bg-slate-800 border border-slate-600 p-2.5 rounded text-sm text-white focus:outline-none focus:border-blue-500" rows="3" placeholder="Ej: Cliente llama indicando robo en proceso..." required></textarea>
        </div>
        
        <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
            <button type="button" onclick="document.getElementById('manualEventModal').close()" class="text-slate-400 hover:text-white px-4 text-xs font-bold uppercase transition">Cancelar</button>
            <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-6 py-2 rounded font-bold text-xs uppercase shadow-lg transition flex items-center gap-2">
                <span>⚡</span> Crear Incidente Ahora
            </button>
        </div>
    </form>
</dialog>

<script>
    // Recarga automática simple (Fase 1)
    setTimeout(() => window.location.reload(), 15000);
</script>
@endsection