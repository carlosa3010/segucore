<div class="w-full md:w-1/2 bg-black/40 flex flex-col">
        <div class="p-6 border-b border-gray-800">
            <h3 class="font-bold text-white flex items-center gap-2">
                <i class="fas fa-chart-pie text-blue-500"></i> Historial y Reportes
            </h3>
        </div>
        <div class="p-6 flex-1">
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Desde</label>
                    <div class="relative">
                        <input type="text" id="start_date" class="datepicker w-full bg-gray-800 border border-gray-600 rounded p-3 pl-10 text-white text-sm outline-none focus:border-blue-500" 
                               value="{{ now()->setTimezone('America/Caracas')->startOfMonth()->format('Y-m-d H:i') }}">
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

                <div class="grid grid-cols-2 gap-3 mt-6">
                    <button onclick="fetchHistory({{ $device->id }})" id="btn-history" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded shadow-lg shadow-blue-900/50 transition flex flex-col items-center justify-center gap-1">
                        <i class="fas fa-map-marked-alt text-lg"></i>
                        <span class="text-[10px] uppercase">Ver en Mapa</span>
                    </button>

                    <button onclick="downloadPdf({{ $device->id }})" id="btn-pdf" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded shadow-lg shadow-red-900/50 transition flex flex-col items-center justify-center gap-1">
                        <i class="fas fa-file-pdf text-lg"></i>
                        <span class="text-[10px] uppercase">Descargar PDF</span>
                    </button>
                </div>
                
                <p class="text-[10px] text-gray-500 text-center mt-2">* Máximo 30 días de consulta.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para manejar la descarga del PDF
    function downloadPdf(deviceId) {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        
        // Abrir en nueva pestaña para forzar descarga
        const url = `/api/history/${deviceId}/pdf?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
        window.open(url, '_blank');
    }
</script>