@extends('layouts.admin')
@section('title', 'Historial de Recorrido')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="h-[calc(100vh-100px)] flex flex-col bg-slate-900 p-4">
    
    <div class="bg-slate-800 p-4 rounded-t-lg border border-slate-700 flex flex-wrap items-end gap-4 shadow-lg z-10">
        <div class="text-white">
            <h1 class="text-lg font-bold flex items-center gap-2">
                <span>📜</span> {{ $device->name }}
            </h1>
            <p class="text-xs text-slate-400 font-mono">IMEI: {{ $device->imei }}</p>
        </div>

        <div class="flex-1"></div>

        <div>
            <label class="block text-[10px] text-slate-400 uppercase mb-1 font-bold">Desde</label>
            <input type="datetime-local" id="dateFrom" class="bg-slate-900 border border-slate-600 text-white text-xs rounded p-2 focus:border-blue-500 outline-none">
        </div>
        
        <div>
            <label class="block text-[10px] text-slate-400 uppercase mb-1 font-bold">Hasta</label>
            <input type="datetime-local" id="dateTo" class="bg-slate-900 border border-slate-600 text-white text-xs rounded p-2 focus:border-blue-500 outline-none">
        </div>

        <div class="flex gap-2">
            <button onclick="loadHistory()" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-4 py-2.5 rounded h-[34px] transition shadow flex items-center gap-2">
                <span>🔍</span> MAPA
            </button>

            <div class="flex bg-slate-700 rounded overflow-hidden border border-slate-600 h-[34px]">
                <button onclick="downloadPDF('detailed')" class="bg-slate-700 hover:bg-slate-600 text-white text-[10px] font-bold px-3 py-2 border-r border-slate-600 transition">
                    📄 DETALLADO
                </button>
                <button onclick="downloadPDF('summary')" class="bg-slate-700 hover:bg-slate-600 text-white text-[10px] font-bold px-3 py-2 transition">
                    📊 RESUMIDO
                </button>
            </div>
        </div>
        
        <a href="{{ route('admin.gps.devices.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold px-4 py-2.5 rounded h-[34px] transition shadow ml-2">
            VOLVER
        </a>
    </div>

    <div class="flex-1 bg-slate-800 border border-t-0 border-slate-700 relative rounded-b-lg overflow-hidden shadow-xl">
        <div id="historyMap" class="w-full h-full z-0"></div>
        
        <div id="routeStats" class="hidden absolute bottom-4 left-4 bg-slate-900/95 backdrop-blur-sm text-white p-4 rounded-lg border border-slate-600 z-[400] text-xs shadow-2xl min-w-[200px]">
            <span class="block font-bold text-slate-400 uppercase mb-2 border-b border-slate-700 pb-1">Resumen del Trayecto</span>
            <div id="statsContent" class="space-y-1">Calculando...</div>
        </div>

        <div id="mapLegend" class="hidden absolute bottom-4 right-4 bg-slate-900/90 p-3 rounded-lg border border-slate-600 z-[400] text-[10px] text-gray-300 shadow-xl w-32">
            <h6 class="font-bold text-white mb-2 text-center uppercase">Leyenda</h6>
            <div class="space-y-1">
                <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-black border border-gray-500"></span> <span>Detenido</span></div>
                <div class="flex items-center gap-2"><span class="w-6 h-1 rounded bg-green-500"></span> <span>1-40 km/h</span></div>
                <div class="flex items-center gap-2"><span class="w-6 h-1 rounded bg-yellow-500"></span> <span>40-80 km/h</span></div>
                <div class="flex items-center gap-2"><span class="w-6 h-1 rounded bg-red-600"></span> <span>+80 km/h</span></div>
            </div>
        </div>
    </div>
</div>

<script>
    var map = L.map('historyMap').setView([10.0, -69.0], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    var historyLayer = L.layerGroup().addTo(map);
    
    // Fechas por defecto
    const now = new Date();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0);
    const toLocalISO = (d) => { return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0') + 'T' + String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0'); };
    
    document.getElementById('dateFrom').value = toLocalISO(todayStart);
    document.getElementById('dateTo').value = toLocalISO(now);

    function loadHistory() {
        let from = document.getElementById('dateFrom').value;
        let to = document.getElementById('dateTo').value;

        if(!from || !to) return alert('Por favor seleccione un rango de fechas válido.');

        // Limpiar mapa
        historyLayer.clearLayers();
        document.getElementById('routeStats').classList.add('hidden');
        document.getElementById('mapLegend').classList.add('hidden');

        fetch("{{ route('admin.gps.devices.history-data', $device->id) }}?from=" + from + "&to=" + to)
            .then(res => res.json())
            .then(data => {
                if(data.length === 0) {
                    alert('No se encontraron datos de recorrido en este rango.');
                    return;
                }

                drawAdvancedRoute(data);

                // Mostrar Stats
                document.getElementById('routeStats').classList.remove('hidden');
                document.getElementById('mapLegend').classList.remove('hidden');
                document.getElementById('statsContent').innerHTML = `
                    <div class="flex justify-between"><span>Puntos:</span> <b>${data.length}</b></div>
                    <div class="flex justify-between"><span>Inicio:</span> <b>${data[0].time.split(' ')[1]}</b></div>
                    <div class="flex justify-between"><span>Fin:</span> <b>${data[data.length-1].time.split(' ')[1]}</b></div>
                `;
            })
            .catch(err => alert('Error al cargar datos.'));
    }

    function drawAdvancedRoute(positions) {
        if(positions.length < 2) return;

        // Ajustar zoom para ver toda la ruta
        const allPoints = positions.map(p => [p.lat, p.lng]);
        map.fitBounds(L.polyline(allPoints).getBounds(), {padding: [50, 50]});

        // Iterar y dibujar segmentos coloridos
        for (let i = 0; i < positions.length - 1; i++) {
            let p1 = positions[i];
            let p2 = positions[i+1];
            
            // Determinar color
            let color = '#000'; // Default black (parado)
            if (p1.speed > 0) {
                if (p1.speed < 40) color = '#10B981'; // Green
                else if (p1.speed < 80) color = '#EAB308'; // Yellow
                else color = '#EF4444'; // Red
            }

            // Si la velocidad es 0, dibujar punto, si no, línea
            if (p1.speed === 0) {
                 L.circleMarker([p1.lat, p1.lng], { radius: 2, color: '#000', opacity: 0.5 }).addTo(historyLayer);
            }

            // Dibujar línea del segmento
            L.polyline([[p1.lat, p1.lng], [p2.lat, p2.lng]], { 
                color: color, 
                weight: 5, 
                opacity: 0.8 
            }).addTo(historyLayer)
              .bindTooltip(`Vel: ${p1.speed} km/h<br>${p1.time}`, { sticky: true });
        }

        // Marcadores Inicio y Fin
        L.marker([positions[0].lat, positions[0].lng], { icon: createIcon('green', 'play') })
            .addTo(historyLayer).bindPopup("Inicio: " + positions[0].time);

        L.marker([positions[positions.length-1].lat, positions[positions.length-1].lng], { icon: createIcon('red', 'flag') })
            .addTo(historyLayer).bindPopup("Fin: " + positions[positions.length-1].time).openPopup();
    }

    function createIcon(colorName, iconName) {
        // Mapeo simple de colores a clases Tailwind aproximadas para el icono HTML
        let bgClass = colorName === 'green' ? 'bg-green-600' : 'bg-red-600';
        
        return L.divIcon({
            html: `<div class="${bgClass} text-white rounded-full p-1 w-8 h-8 flex items-center justify-center shadow border-2 border-white" style="font-size:12px;">
                    🏁
                   </div>`,
            className: 'bg-transparent', 
            iconSize: [32, 32], 
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
    }

    function downloadPDF(type) {
        let from = document.getElementById('dateFrom').value;
        let to = document.getElementById('dateTo').value;
        if(!from || !to) return alert('Seleccione rango de fechas.');
        
        let url = "{{ route('admin.gps.devices.history.pdf', $device->id) }}?from=" + from + "&to=" + to + "&report_type=" + type;
        window.open(url, '_blank');
    }
</script>
@endsection
