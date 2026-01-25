<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="flex items-center gap-4 mb-4">
            <div class="bg-blue-600/20 p-3 rounded-full text-blue-400">
                <i class="fas fa-car text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $device->name }}</h2>
                <p class="text-sm text-gray-400">IMEI: {{ $device->imei }}</p>
            </div>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">Estado</span>
                <span class="{{ $device->status == 'online' ? 'text-green-400' : 'text-red-400' }} font-bold uppercase">
                    {{ $device->status }}
                </span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">Placa</span>
                <span class="text-white">{{ $device->plate_number ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">Conductor</span>
                <span class="text-white">{{ $device->driver->first_name ?? 'Sin asignar' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">Última Conexión</span>
                <span class="text-white">{{ \Carbon\Carbon::parse($device->last_connection)->format('d/m/Y H:i A') }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">Velocidad Actual</span>
                <span class="text-blue-400 font-bold">{{ $device->speed }} km/h</span>
            </div>
        </div>
    </div>

    <div class="bg-gray-700/30 p-4 rounded-lg border border-gray-600">
        <h4 class="font-bold text-white mb-3 border-b border-gray-600 pb-2"><i class="fas fa-route"></i> Consultar Recorrido</h4>
        
        <form onsubmit="event.preventDefault(); submitHistory();">
            <div class="mb-3">
                <label class="block text-xs text-gray-400 mb-1">Desde</label>
                <input type="text" id="history_start" class="datepicker w-full bg-gray-800 border border-gray-600 rounded px-2 py-1 text-white text-sm" value="{{ now()->startOfDay()->format('Y-m-d H:i') }}">
            </div>
            
            <div class="mb-4">
                <label class="block text-xs text-gray-400 mb-1">Hasta</label>
                <input type="text" id="history_end" class="datepicker w-full bg-gray-800 border border-gray-600 rounded px-2 py-1 text-white text-sm" value="{{ now()->endOfDay()->format('Y-m-d H:i') }}">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded transition shadow-lg shadow-blue-900/50">
                <i class="fas fa-search-location"></i> Ver en Mapa
            </button>
        </form>
    </div>
</div>

<script>
    function submitHistory() {
        const start = document.getElementById('history_start').value;
        const end = document.getElementById('history_end').value;
        const deviceId = {{ $device->id }};
        
        // Llamar a la función global definida en map.blade.php
        window.loadHistory(deviceId, start, end);
    }
</script>