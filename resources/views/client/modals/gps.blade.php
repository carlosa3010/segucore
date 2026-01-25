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
                            <span class="text-xs bg-gray-800 text-gray-300 px-2 py-0.5 rounded border border-gray-700">
                                {{ $device->plate_number ?? 'S/P' }}
                            </span>
                            <span class="text-xs {{ $device->status == 'online' ? 'text-green-400' : 'text-red-400' }} font-bold uppercase flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full {{ $device->status == 'online' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ $device->status }}
                            </span>
                        </div>
                    </div>
                </div>
                <button onclick="closeModal()" class="text-gray-500 hover:text-white transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
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
                    <span class="text-lg font-mono text-white">{{ number_format(($device->odometer ?? 0) / 1000, 0) }} <small class="text-xs text-gray-400">km</small></span>
                </div>
            </div>

            <div class="bg-gray-800/30 rounded p-3 border border-gray-800">
                <div class="flex justify-between py-2 border-b border-gray-700/50">
                    <span class="text-gray-400 text-sm">Conductor</span>
                    <span class="text-white text-sm font-semibold">{{ optional($device->driver)->first_name ?? 'No asignado' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-700/50">
                    <span class="text-gray-400 text-sm">Último Reporte</span>
                    <span class="text-white text-sm">{{ \Carbon\Carbon::parse($device->last_connection)->diffForHumans() }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-700/50">
                    <span class="text-gray-400 text-sm">Coordenadas</span>
                    <span class="text-gray-500 text-xs font-mono mt-1">{{ number_format($device->last_latitude, 5) }}, {{ number_format($device->last_longitude, 5) }}</span>
                </div>
                <div class="flex justify-between py-2 pt-2">
                    <span class="text-gray-400 text-sm">Ignición</span>
                    <span class="{{ ($device->ignition ?? false) ? 'text-green-400' : 'text-gray-500' }} text-sm font-bold">
                        {{ ($device->ignition ?? false) ? 'ENCENDIDO' : 'APAGADO' }}
                    </span>
                </div>
            </div>

            <div class="mt-4">
                <h4 class="text-xs font-bold text-gray-500 uppercase mb-3 border-b border-gray-700 pb-1">Comandos Remotos</h4>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="sendCommand('engineStop')" class="flex flex-col items-center justify-center p-3 rounded bg-red-900/20 border border-red-900/50 hover:bg-red-900/40 text-red-400 hover:text-white transition group">
                        <i class="fas fa-power-off text-xl mb-1 group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold">CORTE MOTOR</span>
                    </button>
                    <button onclick="sendCommand('engineResume')" class="flex flex-col items-center justify-center p-3 rounded bg-green-900/20 border border-green-900/50 hover:bg-green-900/40 text-green-400 hover:text-white transition group">
                        <i class="fas fa-plug text-xl mb-1 group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold">RESTAURAR</span>
                    </button>
                </div>
                <div id="command-feedback" class="mt-2 text-center text-xs h-4"></div>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/2 bg-black/40 flex flex-col">
        <div class="p-6 border-b border-gray-800">
            <h3 class="font-bold text-white flex items-center gap-2">
                <i class="fas fa-route text-blue-500"></i> Historial de Recorrido
            </h3>
            <p class="text-xs text-gray-500 mt-1">Selecciona un rango para ver la ruta en el mapa.</p>
        </div>

        <div class="p-6 flex-1">
            <form onsubmit="event.preventDefault(); fetchHistory();" id="historyForm" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Fecha Inicio</label>
                    <div class="relative">
                        <input type="text" id="start_date" class="datepicker w-full bg-gray-800 border border-gray-600 rounded p-3 pl-10 text-white text-sm focus:border-blue-500 focus:outline-none transition" value="{{ now()->startOfDay()->format('Y-m-d H:i') }}">
                        <i class="fas fa-calendar absolute left-3 top-3.5 text-gray-500"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Fecha Fin</label>
                    <div class="relative">
                        <input type="text" id="end_date" class="datepicker w-full bg-gray-800 border border-gray-600 rounded p-3 pl-10 text-white text-sm focus:border-blue-500 focus:outline-none transition" value="{{ now()->endOfDay()->format('Y-m-d H:i') }}">
                        <i class="fas fa-calendar absolute left-3 top-3.5 text-gray-500"></i>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" id="btn-history" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded shadow-lg shadow-blue-900/50 transition flex items-center justify-center gap-2">
                        <i class="fas fa-search-location"></i> CONSULTAR RUTA
                    </button>
                </div>
            </form>
            
            <div class="mt-6 p-4 bg-yellow-900/10 border border-yellow-900/30 rounded text-xs text-yellow-500 flex gap-2">
                <i class="fas fa-info-circle mt-0.5"></i>
                <p>El historial está limitado a rangos máximos de 3 días para garantizar la velocidad de carga.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Lógica de Historial
    function fetchHistory() {
        const btn = document.getElementById('btn-history');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> CARGANDO DATOS...';

        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        const id = {{ $device->id }};

        fetch(`/api/history/${id}?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`)
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    alert(data.error);
                } else if(data.positions.length === 0) {
                    alert("No se encontraron movimientos en este rango.");
                } else {
                    // Cerrar modal y dibujar en mapa principal
                    closeModal();
                    if(window.loadHistoryTrace) {
                        window.loadHistoryTrace(data.positions);
                    } else {
                        console.error("Función loadHistoryTrace no definida en map.blade.php");
                    }
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error de conexión al obtener historial.");
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }

    // 2. Lógica de Comandos
    function sendCommand(type) {
        if(!confirm('¿Está seguro de enviar este comando al dispositivo?')) return;

        const feedback = document.getElementById('command-feedback');
        feedback.innerHTML = '<span class="text-blue-400"><i class="fas fa-circle-notch fa-spin"></i> Enviando...</span>';

        fetch('{{ route("client.device.command", $device->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type: type })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                feedback.innerHTML = '<span class="text-green-500"><i class="fas fa-check"></i> Comando enviado con éxito.</span>';
            } else {
                feedback.innerHTML = '<span class="text-red-500"><i class="fas fa-times"></i> Error: ' + (data.message || 'Fallo desconocido') + '</span>';
            }
        })
        .catch(err => {
            feedback.innerHTML = '<span class="text-red-500"><i class="fas fa-wifi"></i> Error de conexión.</span>';
        });
    }
</script>