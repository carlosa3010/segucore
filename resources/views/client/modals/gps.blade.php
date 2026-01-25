<div class="flex flex-col md:flex-row h-full bg-neutral-900 text-gray-100 overflow-hidden">
    
    <div class="w-full md:w-1/2 flex flex-col border-r border-gray-800">
        
        <div class="p-6 border-b border-gray-800 bg-black/20">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-lg bg-gray-800 border border-gray-700 shadow-lg">
                        <i class="fas fa-car-side text-2xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-wide">{{ $device->name }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs bg-gray-800 text-gray-300 px-2 py-0.5 rounded border border-gray-700">{{ $device->plate_number ?? 'S/P' }}</span>
                            <span class="text-xs {{ ($device->status == 'online' || $device->status == 'moving') ? 'text-green-400' : 'text-red-400' }} font-bold uppercase flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full {{ ($device->status == 'online' || $device->status == 'moving') ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ $device->status }}
                            </span>
                        </div>
                    </div>
                </div>
                <button onclick="closeModal()" class="text-gray-500 hover:text-white transition"><i class="fas fa-times text-xl"></i></button>
            </div>
        </div>

        <div class="p-6 space-y-4 flex-1 overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-800/50 p-3 rounded border border-gray-700/50">
                    <span class="block text-xs text-gray-500 uppercase mb-1">Velocidad</span>
                    <span class="text-lg font-mono text-white">{{ round($device->speed) }} <small class="text-xs text-gray-400">km/h</small></span>
                </div>
                <div class="bg-gray-800/50 p-3 rounded border border-gray-700/50">
                    <span class="block text-xs text-gray-500 uppercase mb-1">Odómetro</span>
                    <span class="text-lg font-mono text-white">{{ number_format($device->odometer ?? 0, 0) }} <small class="text-xs text-gray-400">km</small></span>
                </div>
            </div>

            <div class="bg-gray-800/30 rounded p-3 border border-gray-800">
                <div class="flex justify-between py-2 border-b border-gray-700/50">
                    <span class="text-gray-400 text-sm">Ignición</span>
                    <span class="{{ ($device->ignition ?? false) ? 'text-green-400 font-bold' : 'text-gray-500' }}">
                        {{ ($device->ignition ?? false) ? 'ENCENDIDO' : 'APAGADO' }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-700/50">
                    <span class="text-gray-400 text-sm">Conductor</span>
                    <span class="text-white text-sm">{{ optional($device->driver)->first_name ?? 'No asignado' }}</span>
                </div>
                <div class="flex justify-between py-2 pt-2">
                    <span class="text-gray-400 text-sm">Último Reporte</span>
                    <span class="text-white text-sm font-mono">
                        {{ \Carbon\Carbon::parse($device->last_connection)->setTimezone('America/Caracas')->format('d/m/Y h:i:s A') }}
                    </span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-800">
                <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Acciones Remotas</h4>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="sendCommand({{ $device->id }}, 'engineStop')" class="flex flex-col items-center justify-center p-3 rounded bg-red-900/20 border border-red-900/50 hover:bg-red-900/40 text-red-400 hover:text-white transition group">
                        <i class="fas fa-power-off text-xl mb-1 group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold">APAGAR MOTOR</span>
                    </button>
                    <button onclick="sendCommand({{ $device->id }}, 'engineResume')" class="flex flex-col items-center justify-center p-3 rounded bg-green-900/20 border border-green-900/50 hover:bg-green-900/40 text-green-400 hover:text-white transition group">
                        <i class="fas fa-plug text-xl mb-1 group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold">RESTAURAR</span>
                    </button>
                </div>
                <div id="command-feedback" class="mt-3 text-center text-xs min-h-[20px]"></div>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/2 bg-black/40 flex flex-col">
        <div class="p-6 border-b border-gray-800">
            <h3 class="font-bold text-white flex items-center gap-2">
                <i class="fas fa-route text-blue-500"></i> Historial de Recorrido
            </h3>
        </div>
        <div class="p-6 flex-1">
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Desde</label>
                    <div class="relative">
                        <input type="text" id="start_date" class="datepicker w-full bg-gray-800 border border-gray-600 rounded p-3 pl-10 text-white text-sm outline-none focus:border-blue-500" 
                               value="{{ now()->setTimezone('America/Caracas')->startOfDay()->format('Y-m-d H:i') }}">
                        <i class="fas fa-calendar absolute left-3 top-3.5 text-gray-500"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Hasta</label>
                    <div class="relative">
                        <input type="text" id="end_date" class="datepicker w-full bg-gray-800 border border-gray-600 rounded p-3 pl-10 text-white text-sm outline-none focus:border-blue-500" 
                               value="{{ now()->setTimezone('America/Caracas')->endOfDay()->format('Y-m-d H:i') }}">
                        <i class="fas fa-calendar absolute left-3 top-3.5 text-gray-500"></i>
                    </div>
                </div>

                <div class="pt-4">
                    <button onclick="fetchHistory({{ $device->id }})" id="btn-history" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded shadow-lg shadow-blue-900/50 transition flex items-center justify-center gap-2">
                        <i class="fas fa-search-location"></i> CONSULTAR RUTA
                    </button>
                </div>
                
                <div class="bg-gray-800/50 p-3 rounded border border-gray-700 mt-4 text-[10px] text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i> Puedes consultar hasta 30 días de historial. El reporte PDF estará disponible en el mapa una vez cargada la ruta.
                </div>
            </div>
        </div>
    </div>
</div>