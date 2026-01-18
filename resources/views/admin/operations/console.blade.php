@extends('layouts.admin')

@section('title', 'Consola de Operaciones')

@section('content')
<div class="h-full flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <span class="animate-pulse text-red-500">●</span> Consola de Monitoreo
            </h1>
            <p class="text-xs text-gray-500 mt-1">Cola de eventos priorizada por SLA</p>
        </div>
        <div class="bg-gray-800 px-4 py-2 rounded border border-gray-700 text-sm text-gray-300">
            Operador: <span class="text-white font-bold">{{ Auth::user()->name ?? 'Invitado' }}</span>
        </div>
    </div>

    <div class="flex-1 bg-[#1e293b] border border-gray-700 rounded-lg overflow-hidden flex flex-col">
        
        <div class="bg-gray-800 px-6 py-3 border-b border-gray-700 flex text-xs font-bold text-gray-400 uppercase tracking-wider">
            <div class="w-24">Hora</div>
            <div class="w-24">Prioridad</div>
            <div class="w-32">Cuenta</div>
            <div class="flex-1">Evento / Cliente</div>
            <div class="w-32 text-right">Acción</div>
        </div>

        <div class="overflow-y-auto flex-1 p-0 space-y-px bg-gray-900">
            @forelse($pendingEvents as $event)
                <div class="flex items-center px-6 py-4 bg-[#1e293b] hover:bg-gray-800 transition border-l-4 
                    {{ $event->priority >= 5 ? 'border-l-red-500 bg-red-900/10' : ($event->priority == 4 ? 'border-l-orange-500' : 'border-l-gray-600') }}">
                    
                    <div class="w-24 text-white font-mono text-sm">
                        {{ $event->created_at->format('H:i:s') }}
                        <div class="text-[10px] text-gray-500">{{ $event->created_at->diffForHumans() }}</div>
                    </div>

                    <div class="w-24">
                        @if($event->priority >= 5)
                            <span class="px-2 py-1 rounded bg-red-600 text-white text-xs font-bold animate-pulse">CRÍTICO</span>
                        @elseif($event->priority == 4)
                            <span class="px-2 py-1 rounded bg-orange-600 text-white text-xs font-bold">ALTA</span>
                        @else
                            <span class="px-2 py-1 rounded bg-gray-700 text-gray-300 text-xs">NORMAL</span>
                        @endif
                    </div>

                    <div class="w-32 text-gray-300 font-mono font-bold">
                        {{ $event->account_number }}
                    </div>

                    <div class="flex-1 min-w-0 pr-4">
                        <div class="text-white font-bold text-lg truncate flex items-center gap-2">
                            <span>{{ $event->priority >= 5 ? '🔥' : '⚠️' }}</span>
                            {{ $event->sia_code_description ?? 'Evento SIA ' . $event->event_code }}
                        </div>
                        <div class="text-sm text-gray-400 truncate">
                            {{ $event->account->customer->full_name ?? 'Cliente Desconocido' }} 
                            <span class="mx-1 text-gray-600">|</span> 
                            Zona: {{ $event->zone }}
                        </div>
                    </div>

                    <div class="w-32 text-right">
                        <form action="{{ route('admin.incidents.take', $event->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold py-2 px-4 rounded shadow-lg transition transform hover:scale-105 flex items-center gap-2 ml-auto">
                                <span>Atender</span> &rarr;
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-gray-500 py-20">
                    <div class="text-6xl mb-4 opacity-20">✅</div>
                    <h3 class="text-xl font-bold text-gray-400">Todo en orden</h3>
                    <p class="text-sm">No hay alarmas pendientes en la cola.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    setTimeout(function(){
       window.location.reload();
    }, 15000); // Refresca cada 15 segundos (Idealmente usaríamos Livewire o AJAX aquí en Fase 2)
</script>
@endsection