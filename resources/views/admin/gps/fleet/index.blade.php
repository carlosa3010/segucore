@extends('layouts.admin')
@section('title', 'Gestión de Flota')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="h-[calc(100vh-100px)] flex flex-col md:flex-row gap-4 bg-slate-900 p-4">
    
    <div class="w-full md:w-80 flex flex-col bg-slate-800 rounded-lg border border-slate-700 shadow-xl overflow-hidden shrink-0">
        
        <div class="p-4 bg-slate-900 border-b border-slate-700">
            <h2 class="text-white font-bold mb-2">Estado de Flota</h2>
            <div class="flex justify-between text-xs text-center">
                <div class="bg-blue-900/30 text-blue-300 rounded px-2 py-1">
                    <span class="block font-bold text-lg">{{ $stats['total'] }}</span> Total
                </div>
                <div class="bg-green-900/30 text-green-300 rounded px-2 py-1">
                    <span class="block font-bold text-lg">{{ $stats['online'] }}</span> Online
                </div>
                <div class="bg-red-900/30 text-red-300 rounded px-2 py-1">
                    <span class="block font-bold text-lg">{{ $stats['offline'] }}</span> Offline
                </div>
            </div>
            
            <div class="mt-3 relative">
                <input type="text" id="searchFleet" placeholder="Buscar vehículo..." class="w-full bg-slate-800 text-sm text-white border border-slate-600 rounded pl-8 pr-2 py-1 focus:border-blue-500 outline-none">
                <span class="absolute left-2.5 top-1.5 text-slate-500">🔍</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1" id="vehicleList">
            @foreach($devices as $dev)
            <div class="vehicle-item cursor-pointer p-2 rounded hover:bg-slate-700 transition flex items-center justify-between group"
                 data-id="{{ $dev->id }}" data-name="{{ strtolower($dev->name) }}">
                <div>
                    <span class="block text-sm text-gray-200 font-medium">{{ $dev->name }}</span>
                    <span class="text-[10px] text-gray-500">{{ $dev->plate_number ?? 'S/P' }}</span>
                </div>
                <span class="status-dot w-2 h-2 rounded-full bg-gray-500" id="dot-{{ $dev->id }}"></span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex-1 bg-slate-800 rounded-lg border border-slate-700 relative shadow-xl overflow-hidden">
        <div id="fleetMap" class="w-full h-full z-0"></div>
        
        <div class="absolute top-4 right-4 z-[400] bg-slate-900/80 backdrop-blur text-white text-xs p-2 rounded border border-slate-600">
            <div class="flex items-center gap-2 mb-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> En Movimiento</div>
            <div class="flex items-center gap-2 mb-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Detenido (Online)</div>
            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-500"></span> Sin Señal</div>
        </div>
    </div>

</div>

<script>
    // --- 1. INICIALIZAR MAPA ---
    var map = L.map('fleetMap').setView([10.0, -69.0], 6); // Vista inicial Vzla

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);

    var markers = {}; // Almacén de marcadores { id: markerObj }

    // --- 2. ICONOS ---
    function getIcon(status, speed) {
        let colorUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png'; // Default Offline
        
        if (status === 'online') {
            if (speed > 1) colorUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png'; // Moving
            else colorUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png'; // Stopped
        }

        return L.icon({
            iconUrl: colorUrl,
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
    }

    // --- 3. ACTUALIZAR POSICIONES (AJAX) ---
    function updateFleet() {
        fetch("{{ route('admin.gps.fleet.positions') }}")
            .then(res => res.json())
            .then(data => {
                data.forEach(vehicle => {
                    // Actualizar punto en lista lateral
                    let dot = document.getElementById(`dot-${vehicle.id}`);
                    if(dot) {
                        if(vehicle.status === 'offline') dot.className = "status-dot w-2 h-2 rounded-full bg-gray-500";
                        else if(vehicle.speed > 1) dot.className = "status-dot w-2 h-2 rounded-full bg-green-500 animate-pulse";
                        else dot.className = "status-dot w-2 h-2 rounded-full bg-blue-500";
                    }

                    // Actualizar Marcador en Mapa
                    if (markers[vehicle.id]) {
                        // Si ya existe, movemos suavemente
                        markers[vehicle.id].setLatLng([vehicle.lat, vehicle.lng]);
                        markers[vehicle.id].setIcon(getIcon(vehicle.status, vehicle.speed));
                        markers[vehicle.id].setPopupContent(buildPopup(vehicle));
                    } else {
                        // Crear nuevo marcador
                        var m = L.marker([vehicle.lat, vehicle.lng], {
                            icon: getIcon(vehicle.status, vehicle.speed)
                        }).addTo(map);
                        
                        m.bindPopup(buildPopup(vehicle));
                        
                        // Click en marcador
                        m.on('click', function() {
                            map.setView([vehicle.lat, vehicle.lng], 15);
                        });

                        markers[vehicle.id] = m;
                    }
                });
            });
    }

    function buildPopup(v) {
        return `
            <div class="text-sm">
                <strong class="block text-slate-800 text-lg">${v.name}</strong>
                <span class="text-slate-500">${v.imei}</span>
                <hr class="my-1">
                Velocidad: <b>${v.speed} km/h</b><br>
                Estado: <span class="uppercase font-bold ${v.status=='online'?'text-green-600':'text-gray-500'}">${v.status}</span><br>
                <small>Hace ${v.last_update}</small><br>
                <a href="/admin/gps/devices/${v.id}" class="block mt-2 text-center bg-blue-600 text-white py-1 rounded text-xs">Ver Detalle</a>
            </div>
        `;
    }

    // --- 4. EVENTOS ---
    
    // Click en la lista lateral -> Centrar mapa
    document.querySelectorAll('.vehicle-item').forEach(item => {
        item.addEventListener('click', function() {
            let id = this.getAttribute('data-id');
            if (markers[id]) {
                map.setView(markers[id].getLatLng(), 16);
                markers[id].openPopup();
            } else {
                alert('Este vehículo no tiene ubicación reportada aún.');
            }
        });
    });

    // Buscador
    document.getElementById('searchFleet').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.vehicle-item').forEach(item => {
            let name = item.getAttribute('data-name');
            item.style.display = name.includes(val) ? 'flex' : 'none';
        });
    });

    // Iniciar
    updateFleet();
    setInterval(updateFleet, 10000); // Actualizar cada 10 seg

</script>
@endsection