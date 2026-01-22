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
                <span>🔍</span> CONSULTAR
            </button>

            <button onclick="downloadPDF()" class="bg-red-600 hover:bg-red-500 text-white text-xs font-bold px-4 py-2.5 rounded h-[34px] transition shadow flex items-center gap-2">
                <span>📄</span> PDF
            </button>
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
    </div>
</div>

<script>
    // Configuración Inicial del Mapa
    var map = L.map('historyMap').setView([10.0, -69.0], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);

    var routeLayer = null;
    var startMarker = null;
    var endMarker = null;

    // Fechas por defecto (Hoy 00:00 a Ahora)
    const now = new Date();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0);
    
    // Función auxiliar para formato datetime-local
    const toLocalISO = (d) => { return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0') + 'T' + String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0'); };
    
    document.getElementById('dateFrom').value = toLocalISO(todayStart);
    document.getElementById('dateTo').value = toLocalISO(now);

    // FUNCIÓN: Cargar Historial en el Mapa
    function loadHistory() {
        let from = document.getElementById('dateFrom').value;
        let to = document.getElementById('dateTo').value;

        if(!from || !to) return alert('Por favor seleccione un rango de fechas válido.');

        // Limpiar capas previas
        if(routeLayer) map.removeLayer(routeLayer);
        if(startMarker) map.removeLayer(startMarker);
        if(endMarker) map.removeLayer(endMarker);
        document.getElementById('routeStats').classList.add('hidden');

        // Petición AJAX
        fetch("{{ route('admin.gps.devices.history-data', $device->id) }}?from=" + from + "&to=" + to)
            .then(res => res.json())
            .then(data => {
                if(data.length === 0) {
                    alert('No se encontraron datos de recorrido en este rango de fechas.');
                    return;
                }

                // 1. Dibujar Línea de Ruta (Polilínea Azul)
                const latlngs = data.map(p => [p.lat, p.lng]);
                routeLayer = L.polyline(latlngs, { 
                    color: '#3b82f6', // Azul Tailwind
                    weight: 5,
                    opacity: 0.8,
                    lineCap: 'round'
                }).addTo(map);
                
                // 2. Marcadores de Inicio (Verde) y Fin (Rojo)
                var startIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                });

                var endIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                });

                startMarker = L.marker(latlngs[0], {icon: startIcon}).addTo(map)
                    .bindPopup(`<b>Inicio del Recorrido</b><br>${data[0].time}`);
                
                endMarker = L.marker(latlngs[data.length-1], {icon: endIcon}).addTo(map)
                    .bindPopup(`<b>Fin del Recorrido</b><br>${data[data.length-1].time}`)
                    .openPopup();

                // 3. Ajustar Zoom para ver toda la ruta
                map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });

                // 4. Mostrar Estadísticas
                document.getElementById('routeStats').classList.remove('hidden');
                document.getElementById('statsContent').innerHTML = `
                    <div class="flex justify-between"><span>Puntos:</span> <b>${data.length}</b></div>
                    <div class="flex justify-between"><span>Inicio:</span> <b>${data[0].time}</b></div>
                    <div class="flex justify-between"><span>Fin:</span> <b>${data[data.length-1].time}</b></div>
                `;
            })
            .catch(err => alert('Error al cargar datos. Verifique conexión.'));
    }

    // FUNCIÓN: Descargar PDF (NUEVO)
    function downloadPDF() {
        let from = document.getElementById('dateFrom').value;
        let to = document.getElementById('dateTo').value;
        if(!from || !to) return alert('Seleccione un rango de fechas primero.');
        
        // Redirigir a la ruta de descarga (abre nueva pestaña)
        let url = "{{ route('admin.gps.devices.history.pdf', $device->id) }}?from=" + from + "&to=" + to;
        window.open(url, '_blank');
    }
</script>
@endsection
