@extends('layouts.admin')
@section('title', 'Gestión de Flota')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="h-[calc(100vh-100px)] flex flex-col md:flex-row gap-4 bg-slate-900 p-4">
    
    <div class="w-full md:w-80 flex flex-col bg-slate-800 rounded-lg border border-slate-700 shadow-xl overflow-hidden shrink-0">
        
        <div class="p-4 bg-slate-900 border-b border-slate-700">
            <h2 class="text-white font-bold mb-4 flex items-center gap-2">🚚 Estado de Flota</h2>
            
            <form method="GET" action="{{ route('admin.gps.fleet.index') }}" class="mb-4">
                <select name="customer_id" onchange="this.form.submit()" class="w-full bg-slate-800 text-xs text-white border border-slate-600 rounded p-2 focus:border-blue-500">
                    <option value="">-- Todos los Clientes --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ Str::limit($c->business_name ?? $c->full_name, 25) }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="grid grid-cols-3 gap-2 text-xs text-center mb-3">
                <div class="bg-blue-900/30 text-blue-300 rounded px-1 py-1 border border-blue-900/50">
                    <span class="block font-bold text-lg text-white">{{ $stats['total'] }}</span> Total
                </div>
                <div class="bg-green-900/30 text-green-400 rounded px-1 py-1 border border-green-900/50">
                    <span class="block font-bold text-lg text-white">{{ $stats['online'] }}</span> Online
                </div>
                <div class="bg-red-900/30 text-red-400 rounded px-1 py-1 border border-red-900/50">
                    <span class="block font-bold text-lg text-white">{{ $stats['offline'] }}</span> Offline
                </div>
            </div>
            
            <div class="relative">
                <input type="text" id="searchFleet" placeholder="Filtrar lista..." class="w-full bg-slate-950 text-xs text-white border border-slate-600 rounded pl-7 pr-2 py-2">
                <span class="absolute left-2 top-2 text-slate-500">🔍</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar" id="vehicleList">
            @foreach($devices as $dev)
            <div class="vehicle-item cursor-pointer p-3 rounded hover:bg-slate-700 transition flex items-center justify-between group border border-transparent hover:border-slate-600"
                 data-id="{{ $dev->id }}" data-name="{{ strtolower($dev->name . ' ' . $dev->plate_number) }}">
                <div>
                    <span class="block text-sm text-gray-200 font-bold group-hover:text-white">{{ $dev->name }}</span>
                    <span class="text-[10px] text-gray-500 uppercase">{{ $dev->plate_number ?? 'S/P' }}</span>
                </div>
                <span class="status-dot w-2.5 h-2.5 rounded-full bg-gray-600 border border-gray-900 shadow-sm" id="dot-{{ $dev->id }}"></span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex-1 bg-slate-800 rounded-lg border border-slate-700 relative shadow-xl overflow-hidden">
        <div id="fleetMap" class="w-full h-full z-0"></div>
    </div>
</div>

<script>
    var map = L.map('fleetMap').setView([10.0, -69.0], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(map);
    
    var markers = {};
    const customerId = "{{ request('customer_id') }}";

    function getIcon(status, speed) {
        let colorUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png';
        if (status === 'online') {
            colorUrl = (speed > 1) 
                ? 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png' 
                : 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png';
        }
        return L.icon({ iconUrl: colorUrl, shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] });
    }

    function updateFleet() {
        let url = "{{ route('admin.gps.fleet.positions') }}" + (customerId ? "?customer_id=" + customerId : "");

        fetch(url)
            .then(res => res.json())
            .then(data => {
                data.forEach(v => {
                    // Update List Dot
                    let dot = document.getElementById(`dot-${v.id}`);
                    if(dot) {
                        dot.className = "status-dot w-2.5 h-2.5 rounded-full border border-gray-900 shadow-sm transition-all";
                        if(v.status === 'offline') dot.classList.add('bg-gray-500');
                        else if(v.speed > 1) dot.classList.add('bg-green-500', 'animate-pulse');
                        else dot.classList.add('bg-blue-500');
                    }

                    // Update Map Marker
                    if (markers[v.id]) {
                        markers[v.id].setLatLng([v.lat, v.lng]).setIcon(getIcon(v.status, v.speed));
                        if (markers[v.id].getPopup()) markers[v.id].setPopupContent(buildPopup(v));
                    } else {
                        var m = L.marker([v.lat, v.lng], { icon: getIcon(v.status, v.speed) }).addTo(map);
                        m.bindPopup(buildPopup(v));
                        m.on('click', function() { map.flyTo([v.lat, v.lng], 16); });
                        markers[v.id] = m;
                    }
                });
            });
    }

    function buildPopup(v) {
        // Lógica condicional: Persona (Batería) vs Vehículo (Motor)
        let specificInfo = '';
        
        if (v.type === 'person') {
            // Dispositivo Personal: Mostrar Batería
            let batt = v.battery || 0;
            let battColor = batt < 20 ? 'text-red-500' : 'text-green-600';
            specificInfo = `
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">Batería:</span>
                    <span class="font-bold ${battColor}">${batt}%</span>
                </div>
            `;
        } else {
            // Vehículo: Mostrar Ignición (Motor)
            let ignClass = v.ignition ? 'text-green-600' : 'text-slate-500';
            let ignText = v.ignition ? 'ENCENDIDO' : 'APAGADO';
            specificInfo = `
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">Motor:</span>
                    <span class="font-bold ${ignClass}">${ignText}</span>
                </div>
            `;
        }

        // CORRECCIÓN AQUÍ: Se eliminó "/admin" de los hrefs
        return `
            <div class="text-sm min-w-[180px]">
                <strong class="block text-slate-900 text-base font-bold">${v.name}</strong>
                <span class="text-slate-500 text-xs">${v.plate || 'ID: ' + v.imei}</span>
                
                <div class="my-2 border-t border-gray-200"></div>
                
                <div class="flex justify-between mb-1">
                    <span class="text-gray-500">Velocidad:</span>
                    <span class="font-bold text-gray-800">${v.speed} km/h</span>
                </div>
                
                ${specificInfo}
                
                <div class="grid grid-cols-2 gap-2 mt-3">
                    <a href="/gps/devices/${v.id}/history" target="_blank" 
                       class="text-center bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 py-1.5 rounded text-[10px] font-bold transition">
                        📜 RUTA
                    </a>
                    <a href="/gps/devices/${v.id}" target="_blank" 
                       class="text-center bg-slate-700 hover:bg-slate-600 text-white py-1.5 rounded text-[10px] font-bold transition">
                        🗺️ DETALLES
                    </a>
                </div>
            </div>
        `;
    }

    // Eventos UI
    document.querySelectorAll('.vehicle-item').forEach(item => {
        item.addEventListener('click', function() {
            let id = this.getAttribute('data-id');
            if (markers[id]) {
                map.flyTo(markers[id].getLatLng(), 16);
                markers[id].openPopup();
            } else { alert('Vehículo sin ubicación.'); }
        });
    });

    document.getElementById('searchFleet').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.vehicle-item').forEach(item => {
            item.style.display = item.getAttribute('data-name').includes(val) ? 'flex' : 'none';
        });
    });

    updateFleet();
    setInterval(updateFleet, 10000);
</script>
<style>.custom-scrollbar::-webkit-scrollbar { width: 4px; background: #1e293b; } .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }</style>
@endsection