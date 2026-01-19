@extends('layouts.operations')

@section('title', 'Atención #' . $incident->id)

@section('content')
<div class="h-full grid grid-cols-12 gap-0 bg-slate-950">

    <div class="col-span-3 border-r border-slate-800 flex flex-col bg-slate-900">
        <div id="map" class="h-64 w-full bg-slate-800 z-0 border-b border-slate-700"></div>
        
        <div class="p-4 flex-1 overflow-y-auto">
            <h3 class="text-slate-500 text-xs font-bold uppercase mb-2">Cuenta Abonada</h3>
            <div class="text-2xl font-bold text-white mb-1">{{ $incident->account->account_number }}</div>
            <div class="text-blue-400 font-bold mb-4">{{ $incident->account->customer->business_name ?? $incident->account->customer->full_name }}</div>
            
            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-2">
                    <span class="text-slate-500">📍</span>
                    <span class="text-slate-300">{{ $incident->account->installation_address }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-500">📱</span>
                    <span class="text-slate-300">{{ $incident->account->customer->phone_1 }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-500">🔐</span>
                    <span class="text-red-400 font-mono font-bold">Palabra Clave: {{ $incident->account->customer->monitoring_password }}</span>
                </div>
            </div>

            @if($incident->account->permanent_notes)
            <div class="mt-4 bg-yellow-900/20 border border-yellow-700/50 p-3 rounded text-xs text-yellow-200">
                <strong>⚠️ NOTA OPERATIVA:</strong><br>
                {{ $incident->account->permanent_notes }}
            </div>
            @endif
        </div>
    </div>

    <div class="col-span-6 flex flex-col bg-slate-950">
        
        <div class="p-6 bg-gradient-to-r from-red-900/20 to-slate-900 border-b border-slate-800">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-4xl font-bold text-white">{{ $incident->alarmEvent->event_code }}</span>
                        <span class="text-2xl text-red-400">{{ $incident->alarmEvent->siaCode->description }}</span>
                    </div>
                    <div class="text-lg text-slate-300 font-bold">
                        ZONA {{ $incident->alarmEvent->zone }}: {{ $incident->alarmEvent->zone_name ?? 'Zona sin nombre' }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-mono text-white">{{ $incident->created_at->format('H:i:s') }}</div>
                    <div class="text-xs text-slate-500">{{ $incident->created_at->diffForHumans() }}</div>
                </div>
            </div>
            
            <div class="mt-4 font-mono text-[10px] text-slate-600 bg-black/40 p-1 rounded inline-block">
                RAW: {{ $incident->alarmEvent->raw_data }}
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <h3 class="text-slate-500 text-xs font-bold uppercase mb-3 flex items-center gap-2">
                📞 Lista de Llamadas (Prioridad)
            </h3>
            <div class="grid grid-cols-1 gap-2">
                @foreach($incident->account->customer->contacts->sortBy('priority') as $contact)
                <div class="flex items-center justify-between bg-slate-900 p-3 rounded border border-slate-800 hover:border-slate-600 transition group">
                    <div class="flex items-center gap-3">
                        <div class="bg-slate-700 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            {{ $contact->priority }}
                        </div>
                        <div>
                            <div class="text-white font-bold text-sm">{{ $contact->name }}</div>
                            <div class="text-xs text-slate-500 uppercase">{{ $contact->relationship }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2 py-1 rounded text-xs border border-slate-700" title="Ver Log de Llamadas Antiguas">🕒</button>
                        <a href="tel:{{ $contact->phone }}" class="bg-green-700 hover:bg-green-600 text-white px-4 py-1.5 rounded text-sm font-bold flex items-center gap-2 transition">
                            <span>Llamar</span> <span class="font-mono">{{ $contact->phone }}</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="p-4 bg-slate-900 border-t border-slate-800 grid grid-cols-4 gap-2">
            <button onclick="alert('Módulo SMS en desarrollo')" class="bg-blue-900/30 hover:bg-blue-900/50 text-blue-300 border border-blue-800 py-3 rounded flex flex-col items-center justify-center gap-1 transition">
                <span>💬</span> <span class="text-xs font-bold">SMS Cliente</span>
            </button>
            <button onclick="alert('Módulo Email en desarrollo')" class="bg-blue-900/30 hover:bg-blue-900/50 text-blue-300 border border-blue-800 py-3 rounded flex flex-col items-center justify-center gap-1 transition">
                <span>📧</span> <span class="text-xs font-bold">Email Reporte</span>
            </button>
            <form action="{{ route('admin.incidents.hold', $incident->id) }}" method="POST" class="col-span-1">
                @csrf
                <input type="hidden" name="status" value="police_dispatched">
                <button type="submit" class="w-full bg-red-900/30 hover:bg-red-900/50 text-red-400 border border-red-800 py-3 rounded flex flex-col items-center justify-center gap-1 transition">
                    <span>🚓</span> <span class="text-xs font-bold">Enviar Patrulla</span>
                </button>
            </form>
            <button onclick="document.getElementById('holdModal').showModal()" class="bg-yellow-900/30 hover:bg-yellow-900/50 text-yellow-400 border border-yellow-800 py-3 rounded flex flex-col items-center justify-center gap-1 transition">
                <span>⏸️</span> <span class="text-xs font-bold">Poner en Espera</span>
            </button>
        </div>
    </div>

    <div class="col-span-3 bg-slate-900 border-l border-slate-800 flex flex-col">
        
        <div class="flex border-b border-slate-800">
            <button class="flex-1 py-2 text-xs font-bold text-white border-b-2 border-blue-500 bg-slate-800">Bitácora</button>
            <button class="flex-1 py-2 text-xs font-bold text-slate-500 hover:text-white">Historial (15)</button>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-3 bg-slate-950/50">
            @foreach($incident->logs as $log)
            <div class="text-xs">
                <div class="flex justify-between text-slate-500 mb-0.5">
                    <span class="font-bold {{ $log->action_type == 'SYSTEM' ? 'text-blue-400' : 'text-green-400' }}">{{ $log->action_type }}</span>
                    <span>{{ $log->created_at->format('H:i') }}</span>
                </div>
                <div class="text-slate-300 bg-slate-900 p-2 rounded border border-slate-800">
                    {{ $log->description }}
                    <div class="text-[10px] text-slate-600 mt-1 text-right">Por: {{ $log->user->name }}</div>
                </div>
            </div>
            @endforeach
            
            <div class="text-center text-[10px] text-slate-600 my-4 uppercase tracking-widest border-t border-slate-800 pt-2">
                Últimos Eventos Recibidos
            </div>

            @foreach($accountHistory as $hist)
            <div class="flex justify-between items-center text-xs text-slate-500 opacity-70">
                <span class="font-mono">{{ $hist->created_at->format('d/m H:i') }}</span>
                <span class="font-bold text-slate-400">{{ $hist->event_code }}</span>
                <span class="truncate w-24 text-right">{{ $hist->zone }}</span>
            </div>
            @endforeach
        </div>

        <div class="p-4 bg-slate-900 border-t border-slate-800">
            <form action="{{ route('admin.incidents.close', $incident->id) }}" method="POST">
                @csrf
                <select name="result_code" class="w-full bg-slate-950 border border-slate-700 text-slate-300 text-xs rounded p-2 mb-2">
                    <option value="" disabled selected>Seleccione Resultado...</option>
                    <option value="false_alarm">Falsa Alarma</option>
                    <option value="real_police">Real - Policía Actuó</option>
                    <option value="test">Prueba de Usuario</option>
                </select>
                <textarea name="resolution_notes" rows="2" class="w-full bg-slate-950 border border-slate-700 text-slate-300 text-xs rounded p-2 mb-2" placeholder="Nota de cierre..." required></textarea>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-2 rounded text-sm shadow-lg transition">
                    ✓ CERRAR CASO
                </button>
            </form>
        </div>
    </div>
</div>

<dialog id="holdModal" class="bg-slate-900 text-white p-6 rounded-lg border border-slate-700 shadow-2xl backdrop:bg-black/80">
    <h3 class="font-bold text-lg mb-4">Poner Incidente en Espera</h3>
    <form action="{{ route('admin.incidents.hold', $incident->id) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-xs text-slate-400 mb-1">Motivo</label>
            <select name="status" class="w-full bg-slate-950 border border-slate-700 p-2 rounded">
                <option value="monitoring">Monitoreo Preventivo (Esperando cliente)</option>
                <option value="police_dispatched">Policía en Camino</option>
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-xs text-slate-400 mb-1">Nota para el siguiente operador</label>
            <textarea name="note" class="w-full bg-slate-950 border border-slate-700 p-2 rounded" rows="3"></textarea>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('holdModal').close()" class="text-slate-400 hover:text-white px-4">Cancelar</button>
            <button type="submit" class="bg-yellow-600 hover:bg-yellow-500 text-white px-4 py-2 rounded font-bold">Confirmar Espera</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    // Inicializar Mapa Leaflet
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([{{ $incident->account->latitude ?? 10.065 }}, {{ $incident->account->longitude ?? -69.335 }}], 16);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        L.marker([{{ $incident->account->latitude ?? 10.065 }}, {{ $incident->account->longitude ?? -69.335 }}])
            .addTo(map)
            .bindPopup("<b>{{ $incident->account->branch_name }}</b><br>{{ $incident->account->installation_address }}")
            .openPopup();
    });
</script>
@endpush
@endsection