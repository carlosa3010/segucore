<div class="flex flex-col md:flex-row h-full">
    <div class="w-full md:w-1/2 p-6 border-b md:border-b-0 md:border-r border-gray-700 bg-neutral-900">
        <div class="text-center mb-6">
            <div class="inline-block p-4 rounded-full bg-gray-800 text-white mb-2 shadow-inner">
                <i class="fas fa-car-side text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold text-white tracking-wide">{{ $device->name }}</h2>
            <span class="text-xs text-gray-500 uppercase tracking-widest">{{ $device->plate_number ?? 'SIN PLACA' }}</span>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between items-center text-sm p-2 rounded bg-gray-800/50">
                <span class="text-gray-400">Estado</span>
                @if($device->status == 'online')
                    <span class="text-green-400 font-bold flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> CONECTADO</span>
                @else
                    <span class="text-gray-500 font-bold">DESCONECTADO</span>
                @endif
            </div>
            <div class="flex justify-between items-center text-sm p-2 rounded bg-gray-800/50">
                <span class="text-gray-400">Velocidad</span>
                <span class="text-white font-mono">{{ round($device->speed) }} km/h</span>
            </div>
            <div class="flex justify-between items-center text-sm p-2 rounded bg-gray-800/50">
                <span class="text-gray-400">Conductor</span>
                <span class="text-white">{{ optional($device->driver)->first_name ?? 'No asignado' }}</span>
            </div>
            <div class="flex justify-between items-center text-sm p-2 rounded bg-gray-800/50">
                <span class="text-gray-400">Último Reporte</span>
                <span class="text-white text-xs">{{ \Carbon\Carbon::parse($device->last_connection)->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/2 p-6 bg-black/30">
        <h4 class="text-gray-400 text-xs font-bold uppercase mb-4 border-b border-gray-700 pb-2">Consultar Historial</h4>
        
        <form onsubmit="event.preventDefault(); requestHistory();" id="historyForm">
            <div class="space-y-4">
                <div>
                    <label class="text-gray-500 text-xs block mb-1">Inicio</label>
                    <input type="text" id="start_date" class="datepicker w-full bg-gray-800 border border-gray-700 rounded text-white text-sm px-3 py-2 focus:border-white outline-none" value="{{ now()->startOfDay()->format('Y-m-d H:i') }}">
                </div>
                <div>
                    <label class="text-gray-500 text-xs block mb-1">Fin</label>
                    <input type="text" id="end_date" class="datepicker w-full bg-gray-800 border border-gray-700 rounded text-white text-sm px-3 py-2 focus:border-white outline-none" value="{{ now()->endOfDay()->format('Y-m-d H:i') }}">
                </div>
                
                <button type="submit" id="btn-search" class="w-full bg-white text-black font-bold py-2 rounded hover:bg-gray-200 transition mt-2">
                    <i class="fas fa-route mr-2"></i> VER RECORRIDO
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function requestHistory() {
        let btn = document.getElementById('btn-search');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> BUSCANDO...';
        btn.disabled = true;

        let start = document.getElementById('start_date').value;
        let end = document.getElementById('end_date').value;
        let id = {{ $device->id }};

        fetch(`/portal/api/history/${id}?start=${start}&end=${end}`)
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-route mr-2"></i> VER RECORRIDO';
                
                if(data.error) {
                    alert(data.error);
                } else if (data.positions.length === 0) {
                    alert("No hay datos en este rango de fechas.");
                } else {
                    // Llamamos a la función global en map.blade.php
                    window.loadHistoryTrace(data.positions);
                }
            })
            .catch(err => {
                alert("Error de conexión");
                btn.disabled = false;
                btn.innerHTML = 'REINTENTAR';
            });
    }
</script>