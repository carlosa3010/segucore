@extends('layouts.admin')
@section('title', 'Mapa Táctico - Seguridad Física')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="h-[calc(100vh-80px)] flex flex-col bg-slate-900">
    <div class="bg-slate-800 p-4 border-b border-slate-700 flex justify-between items-center shrink-0">
        <div>
            <h1 class="text-xl font-bold text-white flex items-center gap-2">
                🌐 Mapa Táctico Operativo
                <span class="text-xs bg-red-600 text-white px-2 py-0.5 rounded animate-pulse">EN VIVO</span>
            </h1>
            <p class="text-xs text-slate-400">Visualización unificada: Patrullas, Guardias y K9</p>
        </div>
        <div class="flex gap-4 text-xs font-bold">
            <div class="flex items-center gap-1"><span class="text-xl">🚓</span> Patrullas</div>
            <div class="flex items-center gap-1"><span class="text-xl">👮</span> Guardias</div>
            <div class="flex items-center gap-1 opacity-50"><span class="text-xl">🐕</span> Unitree (Próx)</div>
        </div>
    </div>

    <div class="flex-1 relative z-0">
        <div id="map" class="w-full h-full bg-slate-900"></div>
        
        <div id="loader" class="absolute top-4 right-4 bg-slate-800 text-white text-xs px-3 py-1 rounded shadow z-[1000] hidden">
            🔄 Actualizando...
        </div>
    </div>
</div>

<script>
    // Inicializar Mapa
    var map = L.map('map').setView([10.0, -69.0], 12); // Coordenadas default Venezuela
    
    // Capa Oscura (CartoDB Dark Matter) para look profesional
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    var markers = {}; // Almacén de marcadores para actualizar sin borrar

    function fetchPositions() {
        document.getElementById('loader').classList.remove('hidden');

        fetch("{{ route('admin.security.map.data') }}")
            .then(res => res.json())
            .then(data => {
                // data es un array mezclado de patrullas y guardias
                data.forEach(item => {
                    updateMarker(item);
                });
                document.getElementById('loader').classList.add('hidden');
            })
            .catch(err => console.error(err));
    }

    function updateMarker(obj) {
        var id = obj.id;
        
        // Crear icono personalizado (HTML con Emoji)
        var customIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color: ${obj.type === 'patrol' ? '#3b82f6' : '#22c55e'}; 
                        width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; 
                        border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5); font-size: 16px;">
                        ${obj.icon}
                   </div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        var popupContent = `
            <div class="p-1">
                <strong class="block text-sm uppercase text-blue-600">${obj.name}</strong>
                <span class="text-xs text-gray-600 block">${obj.type === 'patrol' ? 'Placa: ' + (obj.plate||'N/A') : 'Batería: ' + (obj.battery||'?') + '%'}</span>
                ${obj.speed ? `<span class="text-xs font-bold">Vel: ${obj.speed} km/h</span>` : ''}
            </div>
        `;

        if (markers[id]) {
            // Actualizar posición animada
            markers[id].setLatLng([obj.lat, obj.lng]);
            markers[id].setPopupContent(popupContent);
        } else {
            // Crear nuevo
            markers[id] = L.marker([obj.lat, obj.lng], {icon: customIcon})
                .addTo(map)
                .bindPopup(popupContent);
        }
    }

    // Actualizar cada 5 segundos
    fetchPositions();
    setInterval(fetchPositions, 5000);
</script>
@endsection