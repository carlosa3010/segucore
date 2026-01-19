@extends('layouts.operations')

@section('title', 'Consola de Monitoreo')

@section('content')
<div class="h-full flex flex-col bg-slate-900 p-2">
    
    <div class="flex justify-between items-end mb-2 px-2">
        <h1 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="w-3 h-3 bg-red-500 rounded-full animate-ping"></span> 
            Cola de Eventos
        </h1>
        <div class="text-xs text-slate-400">Refresco automático: <span class="text-green-400">Activo</span></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-2 h-full overflow-hidden">
        
        <div class="lg:col-span-3 bg-slate-950 border border-slate-800 rounded flex flex-col overflow-hidden">
            <div class="grid grid-cols-12 gap-2 bg-slate-900 px-4 py-2 text-xs font-bold text-slate-400 uppercase border-b border-slate-800">
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
            <div class="bg-slate-800 px-3 py-2 text-xs font-bold text-white border-b border-slate-700 flex justify-between">
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
                        <div class="text-xs text-slate-300 truncate mb-2">{{ $inc->alarmEvent->siaCode->description }}</div>
                        
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

<script>
    // Recarga automática simple (Fase 1)
    setTimeout(() => window.location.reload(), 15000);
</script>
@endsection