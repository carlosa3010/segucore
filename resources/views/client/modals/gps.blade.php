<div class="flex flex-col bg-gray-900 text-white h-full">
    <div class="flex justify-between items-center p-4 border-b border-gray-800 bg-black/40">
        <div class="flex items-center gap-3">
            <div class="bg-blue-500/20 p-2 rounded text-blue-500">
                <i class="fas fa-car text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">{{ $device->name }}</h3>
                <p class="text-xs text-gray-500">{{ $device->plate_number ?? 'Sin Placa' }}</p>
            </div>
        </div>
        <button onclick="closeModal()" class="text-gray-400 hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-3 text-sm">
            <div class="flex justify-between p-3 bg-gray-800 rounded">
                <span class="text-gray-400">Estado</span>
                <span class="{{ $device->status == 'online' ? 'text-green-400' : 'text-gray-400' }} font-bold uppercase">{{ $device->status }}</span>
            </div>
            <div class="flex justify-between p-3 bg-gray-800 rounded">
                <span class="text-gray-400">Velocidad</span>
                <span class="text-white">{{ round($device->speed) }} km/h</span>
            </div>
            <div class="flex justify-between p-3 bg-gray-800 rounded">
                <span class="text-gray-400">Conductor</span>
                <span class="text-white">{{ optional($device->driver)->first_name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between p-3 bg-gray-800 rounded">
                <span class="text-gray-400">Última posición</span>
                <span class="text-white text-xs">{{ \Carbon\Carbon::parse($device->last_connection)->diffForHumans() }}</span>
            </div>
        </div>

        <div class="bg-gray-800 p-4 rounded border border-gray-700">
            <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Consultar Recorrido</h4>
            <form onsubmit="event.preventDefault(); alert('Funcionalidad de trazado en desarrollo');">
                <div class="mb-2">
                    <input type="text" class="datepicker w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-xs text-white" value="{{ now()->startOfDay() }}">
                </div>
                <div class="mb-3">
                    <input type="text" class="datepicker w-full bg-gray-900 border border-gray-600 rounded px-3 py-2 text-xs text-white" value="{{ now()->endOfDay() }}">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded text-xs transition">
                    BUSCAR
                </button>
            </form>
        </div>
    </div>
</div>