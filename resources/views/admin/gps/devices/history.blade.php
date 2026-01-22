@extends('layouts.admin')
@section('title', 'Historial de Recorrido')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="h-[calc(100vh-100px)] flex flex-col bg-slate-900 p-4">
    
    <div class="bg-slate-800 p-4 rounded-t-lg border border-slate-700 flex flex-wrap items-end gap-4">
        <div class="text-white">
            <h1 class="text-lg font-bold">{{ $device->name }}</h1>
            <p class="text-xs text-slate-400">IMEI: {{ $device->imei }}</p>
        </div>

        <div class="flex-1"></div>

        <div>
            <label class="block text-[10px] text-slate-400 uppercase mb-1">Desde</label>
            <input type="datetime-local" id="dateFrom" class="bg-slate-900 border border-slate-600 text-white text-xs rounded p-2">
        </div>
        
        <div>
            <label class="block text-[10px] text-slate-400 uppercase mb-1">Hasta</label>
            <input type="datetime-local" id="dateTo" class="bg-slate-900 border border-slate-600 text-white text-xs rounded p-2">
        </div>

        <button onclick="loadHistory()" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-4 py-2.5 rounded h-[34px]">
            CONSULTAR RUTA
        </button>
        
        <a href="{{ route('admin.gps.devices.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold px-4 py-2.5 rounded h-[34px]">
            VOLVER
        </a>
    </div>

    <div class="flex-1 bg-slate-800 border border-t-0 border-slate-700 relative rounded-b-lg overflow-hidden">
        <div id="historyMap" class="w-full h-full"></div>
        
        <div id="routeStats" class="hidden absolute bottom-4 left-4 bg-slate-900/90 text-white p-3 rounded border border-slate-600 z-[400] text-xs">
            <span class="block font-bold mb-1">Resumen de Ruta:</span>
            <div id="statsContent">Calculando...</div>
        </div>
    </div>
</div>

<script>
    var map = L.map('historyMap').setView([10.0, -69.0], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    var routeLayer = null;
    var startMarker = null;
    var endMarker = null;

    // Valores por defecto (Hoy 00:00 a Ahora)
    const now = new Date();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0);
    
    // Ajustar formato para input datetime-local (YYYY-MM-DDTHH:MM)
    const toLocalISO = (d) => { return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0') + 'T' + String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0'); };
    
    document.getElementById('dateFrom').value = toLocalISO(todayStart);
    document.getElementById('dateTo').value = toLocalISO(now);

    function loadHistory() {
        let from = document.getElementById('dateFrom').value;
        let to = document.getElementById('dateTo').value;

        if(!from || !to) return alert('Seleccione rango de fechas');

        // Limpiar mapa
        if(routeLayer) map.removeLayer(routeLayer);
        if(startMarker) map.removeLayer(startMarker);
        if(endMarker) map.removeLayer(endMarker);
        document.getElementById('routeStats').classList.add('hidden');

        fetch("{{ route('admin.gps.devices.history-data', $device->id) }}?from=" + from + "&to=" + to)
            .then(res => res.json())
            .then(data => {
                if(data.length === 0) return alert('No hay datos en este rango.');

                // Dibujar Línea
                const latlngs = data.map(p => [p.lat, p.lng]);
                routeLayer = L.polyline(latlngs, { color: 'blue', weight: 4 }).addTo(map);
                
                // Marcadores Inicio y Fin
                startMarker = L.marker(latlngs[0]).addTo(map).bindPopup('Inicio: ' + data[0].time);
                endMarker = L.marker(latlngs[data.length-1]).addTo(map).bindPopup('Fin: ' + data[data.length-1].time).openPopup();

                map.fitBounds(routeLayer.getBounds());

                // Mostrar Stats
                document.getElementById('routeStats').classList.remove('hidden');
                document.getElementById('statsContent').innerHTML = `
                    Puntos: ${data.length}<br>
                    Inicio: ${data[0].time}<br>
                    Fin: ${data[data.length-1].time}
                `;
            });
    }
</script>
@endsection
