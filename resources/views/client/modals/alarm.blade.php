<div class="flex flex-col h-full bg-gray-900 text-white">
    <div class="flex justify-between items-center p-4 border-b border-gray-800 bg-black/40">
        <div class="flex items-center gap-3">
            <div class="bg-red-500/20 p-2 rounded text-red-500">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg leading-tight">{{ $alarm->name ?? 'Cuenta de Alarma' }}</h3>
                <p class="text-xs text-gray-500">ID: {{ $alarm->account_number }}</p>
            </div>
        </div>
        <button onclick="closeModal()" class="text-gray-400 hover:text-white transition">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="space-y-4">
            <div class="bg-gray-800 p-4 rounded border border-gray-700 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Estado del Sistema</p>
                @php 
                    $status = $alarm->monitoring_status ?? 'normal';
                    $statusClass = 'text-gray-400';
                    $statusIcon = 'fa-circle-question';
                    $statusText = 'DESCONOCIDO';
                    
                    if($status == 'armed' || $status == 'closed') {
                        $statusClass = 'text-green-500';
                        $statusIcon = 'fa-lock';
                        $statusText = 'ARMADO / CERRADO';
                    } elseif($status == 'disarmed' || $status == 'open') {
                        $statusClass = 'text-gray-400';
                        $statusIcon = 'fa-lock-open';
                        $statusText = 'DESARMADO / ABIERTO';
                    } elseif($status == 'alarm') {
                        $statusClass = 'text-red-500 animate-pulse';
                        $statusIcon = 'fa-exclamation-triangle';
                        $statusText = 'EN ALARMA';
                    }
                @endphp
                <h2 class="text-2xl font-bold {{ $statusClass }}">
                    <i class="fas {{ $statusIcon }}"></i> {{ $statusText }}
                </h2>
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-800">
                    <span class="text-gray-500">Dirección</span>
                    <span class="text-gray-300 text-right truncate max-w-[200px]">{{ $alarm->installation_address ?? 'No registrada' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-800">
                    <span class="text-gray-500">Última Señal</span>
                    <span class="text-gray-300">
                        @php
                            // ✅ CORRECCIÓN: Priorizar la fecha del último evento recibido
                            $lastDate = $events->count() > 0 ? $events->first()->created_at : $alarm->updated_at;
                        @endphp
                        {{ $lastDate ? $lastDate->setTimezone('America/Caracas')->format('d/m/Y h:i A') : 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-800">
                    <span class="text-gray-500">Particiones</span>
                    <span class="text-gray-300">{{ $alarm->partitions_count ?? 1 }}</span>
                </div>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Últimos Eventos Recibidos</h4>
            <div class="bg-gray-800 rounded border border-gray-700 overflow-hidden h-[250px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-700">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-700/50 transition">
                                <td class="p-3 text-gray-400 text-xs whitespace-nowrap">
                                    {{-- Conversión de Hora: UTC -> America/Caracas --}}
                                    {{ $event->created_at->setTimezone('America/Caracas')->format('d/m H:i') }}
                                    <div class="text-[9px] opacity-50">{{ $event->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-blue-900/30 text-blue-400 font-mono text-xs px-1 rounded">{{ $event->event_code }}</span>
                                        <span class="text-white text-xs">{{ Str::limit($event->siaCode->description ?? 'Señal Recibida', 25) }}</span>
                                    </div>
                                    @if($event->zone)
                                        <div class="text-[10px] text-gray-500 pl-1">Zona: {{ $event->zone }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="p-8 text-center text-gray-500 italic">
                                    <i class="fas fa-history mb-2 text-xl opacity-50"></i><br>
                                    No hay eventos recientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>