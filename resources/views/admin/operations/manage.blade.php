@extends('layouts.operations')

@section('title', 'Atención #' . $incident->id)

@section('content')
<div class="h-full grid grid-cols-12 gap-0 bg-slate-900 text-slate-300">

    <div class="col-span-12 lg:col-span-3 border-r border-slate-700 flex flex-col bg-slate-900">
        <div id="map" class="h-64 w-full bg-slate-200 z-0 border-b border-slate-700 relative shadow-inner"></div>
        
        <div class="p-4 flex-1 overflow-y-auto">
            <h3 class="text-blue-500 text-[10px] font-bold uppercase tracking-widest mb-2">Cuenta Abonada</h3>
            <div class="text-3xl font-bold text-white mb-1 tracking-tight">{{ $incident->alarmEvent->account_number }}</div>
            <div class="text-lg text-slate-200 font-medium leading-tight mb-4">
                {{ $incident->alarmEvent->account->customer->business_name ?? $incident->alarmEvent->account->customer->full_name }}
            </div>
            
            <div class="space-y-4 text-sm mt-6">
                <div class="flex items-start gap-3">
                    <span class="text-slate-500 text-lg">📍</span>
                    <span class="text-slate-300 font-medium">{{ $incident->alarmEvent->account->installation_address }}</span>
                </div>
                
                <div class="p-3 bg-slate-800 rounded border border-slate-700">
                    <div class="text-[10px] text-slate-500 uppercase font-bold mb-1">Palabra Clave</div>
                    <div class="text-xl font-mono font-bold text-red-400 tracking-wider">
                        {{ $incident->alarmEvent->account->customer->monitoring_password ?? 'NO DEFINIDA' }}
                    </div>
                </div>

                @if($incident->alarmEvent->account->permanent_notes)
                <div class="p-3 bg-yellow-900/20 border border-yellow-600/30 rounded">
                    <strong class="text-yellow-500 text-xs uppercase flex items-center gap-1">
                        ⚠️ Nota Permanente
                    </strong>
                    <p class="text-yellow-200 text-xs mt-1">{{ $incident->alarmEvent->account->permanent_notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-span-12 lg:col-span-6 flex flex-col bg-slate-950 relative">
        
        <div class="p-6 bg-gradient-to-b from-slate-900 to-slate-950 border-b border-slate-800">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="bg-red-600 text-white text-sm font-bold px-2 py-1 rounded">PRIORIDAD {{ $incident->alarmEvent->siaCode->priority }}</span>
                        <span class="font-mono text-slate-500 text-xs">ID: #{{ $incident->id }}</span>
                    </div>
                    <h1 class="text-4xl font-black text-white tracking-tight mb-1">
                        {{ $incident->alarmEvent->siaCode->description ?? 'EVENTO DESCONOCIDO' }}
                    </h1>
                    <div class="text-xl text-blue-400 font-medium flex items-center gap-2">
                        <span class="font-bold">ZONA {{ $incident->alarmEvent->zone }}</span>
                        <span class="text-slate-600">|</span>
                        <span>{{ $incident->alarmEvent->zone_name ?? 'Zona General' }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-mono text-white font-thin">{{ $incident->created_at->format('H:i') }}</div>
                    <div class="text-xs text-slate-500 uppercase font-bold tracking-widest">Hora Evento</div>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-800/50">
                <code class="text-[10px] font-mono text-slate-600 break-all">
                    {{ $incident->alarmEvent->raw_data }}
                </code>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-slate-950">
            <h3 class="text-slate-500 text-xs font-bold uppercase mb-4 flex items-center gap-2 px-2">
                👥 Protocolo de Contacto
            </h3>
            
            <div class="space-y-2">
                @foreach($incident->alarmEvent->account->customer->contacts->sortBy('priority') as $contact)
                <div class="flex items-center justify-between bg-slate-900 p-4 rounded-lg border border-slate-800 hover:border-blue-500/50 transition group shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="bg-slate-800 text-slate-300 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border border-slate-700">
                            {{ $contact->priority }}
                        </div>
                        <div>
                            <div class="text-white font-bold text-base">{{ $contact->name }}</div>
                            <div class="text-xs text-slate-500 uppercase font-bold tracking-wider">{{ $contact->relationship }}</div>
                        </div>
                    </div>
                    <a href="tel:{{ $contact->phone }}" class="bg-green-700 hover:bg-green-600 text-white pl-4 pr-6 py-2 rounded-md font-bold flex items-center gap-3 transition shadow-lg shadow-green-900/20 group-hover:translate-x-1">
                        <span>📞</span> <span class="font-mono text-lg">{{ $contact->phone }}</span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>

        <div class="p-4 bg-slate-900 border-t border-slate-800 grid grid-cols-4 gap-3 shadow-[0_-5px_15px_rgba(0,0,0,0.3)] z-10">
            <button onclick="alert('Función SMS en desarrollo')" class="col-span-1 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-md py-3 flex flex-col items-center justify-center gap-1 transition group">
                <span class="group-hover:scale-110 transition">💬</span> <span class="text-[10px] font-bold uppercase">SMS</span>
            </button>
            
            <button onclick="alert('Función Email en desarrollo')" class="col-span-1 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-md py-3 flex flex-col items-center justify-center gap-1 transition group">
                <span class="group-hover:scale-110 transition">📧</span> <span class="text-[10px] font-bold uppercase">Email</span>
            </button>
            
            <form action="{{ route('admin.incidents.hold', $incident->id) }}" method="POST" class="col-span-1 w-full h-full">
                @csrf
                <input type="hidden" name="status" value="police_dispatched">
                <button type="submit" class="w-full h-full bg-red-900/40 hover:bg-red-600 hover:text-white text-red-400 border border-red-900/50 rounded-md py-3 flex flex-col items-center justify-center gap-1 transition group">
                    <span class="text-lg group-hover:scale-110 transition">🚓</span> <span class="text-[10px] font-bold uppercase">Enviar Patrulla</span>
                </button>
            </form>
            
            <button onclick="document.getElementById('holdModal').showModal()" class="col-span-1 bg-yellow-900/40 hover:bg-yellow-600 hover:text-white text-yellow-400 border border-yellow-900/50 rounded-md py-3 flex flex-col items-center justify-center gap-1 transition group">
                <span class="text-lg group-hover:scale-110 transition">⏸️</span> <span class="text-[10px] font-bold uppercase">En Espera</span>
            </button>
        </div>
    </div>

    <div class="col-span-12 lg:col-span-3 bg-slate-900 border-l border-slate-700 flex flex-col h-full overflow-hidden">
        
        <div class="flex border-b border-slate-800 bg-slate-950 shrink-0">
            <button onclick="switchTab('logs')" id="tab-logs" class="flex-1 py-3 text-xs font-bold text-white border-b-2 border-blue-500 bg-slate-800 transition">
                BITÁCORA
            </button>
            <button onclick="switchTab('history')" id="tab-history" class="flex-1 py-3 text-xs font-bold text-slate-500 hover:text-white border-b-2 border-transparent hover:bg-slate-800 transition">
                HISTORIAL
            </button>
        </div>

        <div id="content-logs" class="flex-1 flex flex-col overflow-hidden bg-slate-900">
            
            <div class="p-3 bg-slate-800 border-b border-slate-700 shrink-0 z-10">
                <form action="{{ route('admin.incidents.add-note', $incident->id) }}" method="POST">
                    @csrf
                    <label class="text-[10px] uppercase font-bold text-blue-400 mb-1 block">Agregar Observación</label>
                    <div class="flex gap-2">
                        <input type="text" name="note" class="flex-1 bg-slate-900 border border-slate-600 rounded text-xs px-3 py-2 text-white focus:border-blue-500 outline-none placeholder-slate-500" placeholder="Ej: Llamé y dio ocupado..." required autocomplete="off">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-2 rounded text-xs font-bold transition shadow-lg">
                            ➜
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @forelse($incident->logs as $log)
                    <div class="relative pl-4 border-l-2 
                        {{ $log->action_type == 'SYSTEM' ? 'border-blue-800' : 
                          ($log->action_type == 'NOTE' ? 'border-yellow-500' : 'border-green-600') }}">
                        
                        <div class="text-[10px] text-slate-500 mb-1 flex justify-between">
                            <span>{{ $log->created_at->format('H:i') }}</span>
                            <span class="font-bold 
                                {{ $log->action_type == 'SYSTEM' ? 'text-blue-500' : 
                                  ($log->action_type == 'NOTE' ? 'text-yellow-500' : 'text-green-500') }}">
                                {{ $log->action_type == 'NOTE' ? 'NOTA MANUAL' : $log->action_type }}
                            </span>
                        </div>
                        
                        <div class="text-xs text-slate-300 leading-relaxed bg-slate-800/50 p-2 rounded">
                            {{ $log->description }}
                        </div>
                        
                        <div class="text-[9px] text-slate-600 mt-1 text-right italic">
                            Op: {{ $log->user->name ?? 'Sistema' }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-600 py-10 text-xs">
                        Sin registros. Escribe la primera nota arriba.
                    </div>
                @endforelse
            </div>
        </div>

        <div id="content-history" class="hidden flex-1 overflow-y-auto p-0 bg-slate-900">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-950 text-[10px] uppercase text-slate-500 font-bold sticky top-0">
                    <tr>
                        <th class="p-2">Fecha</th>
                        <th class="p-2">Evento</th>
                        <th class="p-2 text-right">Zona</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-slate-400 divide-y divide-slate-800">
                    @forelse($accountHistory as $hist)
                    <tr class="hover:bg-slate-800 transition">
                        <td class="p-2 font-mono text-[10px] text-slate-500">{{ $hist->created_at->format('d/m H:i') }}</td>
                        <td class="p-2">
                            <span class="font-bold text-slate-300">{{ $hist->event_code }}</span>
                            <span class="block text-[9px] text-slate-500 truncate max-w-[100px]">{{ $hist->siaCode->description ?? '' }}</span>
                        </td>
                        <td class="p-2 text-right font-mono text-blue-400">{{ $hist->zone }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-slate-600 text-xs">Sin eventos recientes</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-950 border-t border-slate-800 shadow-[0_-5px_15px_rgba(0,0,0,0.5)] z-20 shrink-0">
            <h4 class="text-[10px] font-bold text-slate-500 uppercase mb-2">Resolución del Caso</h4>
            <form action="{{ route('admin.incidents.close', $incident->id) }}" method="POST">
                @csrf
                <div class="mb-2">
                    <select name="result_code" class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded p-2 focus:ring-1 focus:ring-blue-500 outline-none" required>
                        <option value="" disabled selected>Seleccione Resultado...</option>
                        @if(isset($resolutions) && $resolutions->count() > 0)
                            @foreach($resolutions as $res)
                                <option value="{{ $res->code }}">{{ $res->name }}</option>
                            @endforeach
                        @else
                            <option value="false_alarm">🚫 Falsa Alarma</option>
                            <option value="real_police">👮 Real - Policía Actuó</option>
                            <option value="real_medical">🚑 Real - Emergencia Médica</option>
                            <option value="test">🔧 Prueba de Usuario</option>
                        @endif
                    </select>
                </div>
                <div class="mb-3">
                    <textarea name="resolution_notes" rows="2" class="w-full bg-slate-900 border border-slate-700 text-white text-xs rounded p-2 focus:ring-1 focus:ring-blue-500 outline-none resize-none" placeholder="Informe final obligatorio..." required></textarea>
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded text-sm shadow-lg shadow-green-900/30 transition flex items-center justify-center gap-2">
                    <span>✓</span> CERRAR INCIDENTE
                </button>
            </form>
        </div>
    </div>
</div>

<dialog id="holdModal" class="m-auto bg-slate-900 text-white p-0 rounded-lg border border-slate-700 shadow-2xl backdrop:bg-black/90 w-full max-w-sm open:animate-fade-in text-left">
    <div class="p-4 border-b border-slate-800 bg-slate-950 flex justify-between items-center">
        <h3 class="font-bold text-sm uppercase text-slate-300">Poner Incidente en Espera</h3>
        <button type="button" onclick="document.getElementById('holdModal').close()" class="text-slate-500 hover:text-white font-bold px-2">✕</button>
    </div>
    
    <form action="{{ route('admin.incidents.hold', $incident->id) }}" method="POST" class="p-6">
        @csrf
        <div class="mb-4">
            <label class="block text-xs text-slate-500 uppercase font-bold mb-2">Motivo</label>
            <select name="status" class="w-full bg-slate-800 border border-slate-600 p-2.5 rounded text-sm text-white focus:outline-none focus:border-blue-500">
                @if(isset($holdReasons) && $holdReasons->count() > 0)
                    @foreach($holdReasons as $reason)
                        <option value="{{ $reason->code }}">{{ $reason->name }}</option>
                    @endforeach
                @else
                    <option value="monitoring">⏳ Monitoreo Preventivo</option>
                    <option value="police_dispatched">🚓 Policía en Camino</option>
                    <option value="waiting_contact">📞 Esperando Contacto</option>
                @endif
            </select>
        </div>
        
        <div class="mb-6">
            <label class="block text-xs text-slate-500 uppercase font-bold mb-2">Nota Interna</label>
            <textarea name="note" class="w-full bg-slate-800 border border-slate-600 p-2.5 rounded text-sm text-white focus:outline-none focus:border-blue-500" rows="3" placeholder="Ej: Llamar en 15 min..."></textarea>
        </div>
        
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('holdModal').close()" class="text-slate-400 hover:text-white px-4 text-xs font-bold uppercase transition">Cancelar</button>
            <button type="submit" class="bg-yellow-600 hover:bg-yellow-500 text-white px-6 py-2 rounded font-bold text-xs uppercase shadow-lg transition">Confirmar</button>
        </div>
    </form>
</dialog>
    
    <form action="{{ route('admin.incidents.hold', $incident->id) }}" method="POST" class="p-6">
        @csrf
        <div class="mb-4">
            <label class="block text-xs text-slate-500 uppercase font-bold mb-2">Motivo</label>
            <select name="status" class="w-full bg-slate-800 border border-slate-600 p-2.5 rounded text-sm text-white focus:outline-none focus:border-blue-500">
                @if(isset($holdReasons) && $holdReasons->count() > 0)
                    @foreach($holdReasons as $reason)
                        <option value="{{ $reason->code }}">{{ $reason->name }}</option>
                    @endforeach
                @else
                    <option value="monitoring">⏳ Monitoreo Preventivo</option>
                    <option value="police_dispatched">🚓 Policía en Camino</option>
                    <option value="waiting_contact">📞 Esperando Contacto</option>
                @endif
            </select>
        </div>
        
        <div class="mb-6">
            <label class="block text-xs text-slate-500 uppercase font-bold mb-2">Nota Interna</label>
            <textarea name="note" class="w-full bg-slate-800 border border-slate-600 p-2.5 rounded text-sm text-white focus:outline-none focus:border-blue-500" rows="3" placeholder="Ej: Llamar en 15 min..."></textarea>
        </div>
        
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('holdModal').close()" class="text-slate-400 hover:text-white px-4 text-xs font-bold uppercase transition">Cancelar</button>
            <button type="submit" class="bg-yellow-600 hover:bg-yellow-500 text-white px-6 py-2 rounded font-bold text-xs uppercase shadow-lg transition">Confirmar</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    // 1. Inicializar Mapa Leaflet (Tema Claro)
    document.addEventListener('DOMContentLoaded', function() {
        // Coordenadas o default
        var lat = {{ $incident->alarmEvent->account->latitude ?? 10.4806 }};
        var lng = {{ $incident->alarmEvent->account->longitude ?? -66.9036 }};

        var map = L.map('map').setView([lat, lng], 16);
        
        // CAPA CLARA (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Marcador Rojo
        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        L.marker([lat, lng], {icon: redIcon})
            .addTo(map)
            .bindPopup("<b>{{ $incident->alarmEvent->account->branch_name }}</b><br>{{ $incident->alarmEvent->account->installation_address }}")
            .openPopup();
    });

    // 2. Lógica de Pestañas (Bitácora vs Historial)
    function switchTab(tab) {
        const btnLogs = document.getElementById('tab-logs');
        const btnHist = document.getElementById('tab-history');
        const contentLogs = document.getElementById('content-logs');
        const contentHist = document.getElementById('content-history');

        if (tab === 'logs') {
            // Activar Logs
            contentLogs.classList.remove('hidden');
            contentHist.classList.add('hidden');
            
            // Estilos Botones
            btnLogs.classList.remove('text-slate-500', 'border-transparent', 'bg-transparent');
            btnLogs.classList.add('text-white', 'border-blue-500', 'bg-slate-800');
            
            btnHist.classList.add('text-slate-500', 'border-transparent');
            btnHist.classList.remove('text-white', 'border-blue-500', 'bg-slate-800');
        } else {
            // Activar Historial
            contentLogs.classList.add('hidden');
            contentHist.classList.remove('hidden');

            // Estilos Botones
            btnHist.classList.remove('text-slate-500', 'border-transparent');
            btnHist.classList.add('text-white', 'border-blue-500', 'bg-slate-800');
            
            btnLogs.classList.add('text-slate-500', 'border-transparent', 'bg-transparent');
            btnLogs.classList.remove('text-white', 'border-blue-500', 'bg-slate-800');
        }
    }
</script>
@endpush
@endsection