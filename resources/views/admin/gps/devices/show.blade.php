@extends('layouts.admin')
@section('title', 'Monitor GPS')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@php
    // Preparar datos iniciales (para la carga rápida de PHP)
    $pos = $liveData ? $liveData->position : null;
    $lat = $pos ? $pos->latitude : 10.0; // Default
    $lon = $pos ? $pos->longitude : -69.0;
    
    // Velocidad: Traccar usa Nudos (Knots). 1 Knot = 1.852 Km/h
    $speedKmh = $pos ? round($pos->speed * 1.852, 1) : 0;
    
    // Atributos JSON (Bateria, Ignición, etc)
    $attrs = $pos ? $pos->attributes : [];
    $battery = $attrs['batteryLevel'] ?? $attrs['battery'] ?? null;
    $ignition = $attrs['ignition'] ?? false;
@endphp

<div class="bg-slate-900 min-h-screen p-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-5 shadow-lg">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $device->name }}</h2>
                        <p class="text-xs text-slate-400">{{ $device->device_model }}</p>
                    </div>
                    <div class="text-right">
                        @if($liveData && $liveData->status == 'online')
                            <span class="bg-green-900/50 text-green-400 border border-green-700 px-2 py-1 rounded text-xs font-bold animate-pulse">ONLINE</span>
                        @else
                            <span class="bg-slate-700 text-slate-400 px-2 py-1 rounded text-xs font-bold">OFFLINE</span>
                        @endif
                    </div>
                </div>
                
                <div class="space-y-3 text-sm border-t border-slate-700 pt-4">
                    <div class="flex justify-between text-slate-300">
                        <span>IMEI:</span> <span class="font-mono text-yellow-500">{{ $device->imei }}</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Placa:</span> <span class="font-bold text-white">{{ $device->plate_number ?? '---' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Último Reporte:</span>
                        <span class="text-white">{{ $liveData ? \Carbon\Carbon::parse($liveData->lastupdate)->format('d/m H:i:s') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            @if($pos)
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-5 shadow-lg">
                <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Telemetría en Vivo</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-900/50 p-3 rounded border border-slate-700 text-center">
                        <span class="block text-2xl font-bold text-blue-400" id="disp-speed">{{ $speedKmh }} <span class="text-xs text-slate-500">km/h</span></span>
                        <span class="text-[10px] text-slate-400 uppercase">Velocidad</span>
                    </div>

                    <div class="bg-slate-900/50 p-3 rounded border border-slate-700 text-center">
                        <span class="block text-xl font-bold text-slate-300">{{ round($pos->altitude) }} <span class="text-xs text-slate-500">m</span></span>
                        <span class="text-[10px] text-slate-400 uppercase">Altitud</span>
                    </div>

                    @if($battery !== null)
                    <div class="bg-slate-900/50 p-3 rounded border border-slate-700 text-center">
                        <span class="block text-xl font-bold {{ $battery < 20 ? 'text-red-500' : 'text-green-400' }}">
                            {{ $battery }}%
                        </span>
                        <span class="text-[10px] text-slate-400 uppercase">Batería Disp.</span>
                    </div>
                    @endif

                    @if($device->vehicle_type == 'car' || $device->vehicle_type == 'truck')
                    <div class="bg-slate-900/50 p-3 rounded border border-slate-700 text-center">
                        <span class="block text-xl font-bold {{ $ignition ? 'text-green-400' : 'text-slate-500' }}">
                            {{ $ignition ? 'ON' : 'OFF' }}
                        </span>
                        <span class="text-[10px] text-slate-400 uppercase">Motor (ACC)</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($device->vehicle_type !== 'person')
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-5 shadow-lg">
                <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Acciones Remotas</h3>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="sendCommand('engineStop')" class="bg-red-900/30 hover:bg-red-700 border border-red-800 text-red-200 py-3 rounded-lg text-xs font-bold transition flex flex-col items-center gap-1 group">
                        <span class="text-lg group-hover:scale-110 transition">🔒</span> 
                        CORTAR CORRIENTE
                    </button>
                    <button onclick="sendCommand('engineResume')" class="bg-green-900/30 hover:bg-green-700 border border-green-800 text-green-200 py-3 rounded-lg text-xs font-bold transition flex flex-col items-center gap-1 group">
                        <span class="text-lg group-hover:scale-110 transition">⚡</span> 
                        HABILITAR MOTOR
                    </button>
                </div>
                <p class="text-[10px] text-slate-500 mt-2 text-center">Solo usar en caso de emergencia.</p>
            </div>
            @endif

        </div>

        <div class="lg:col-span-2">
            <div class="bg-slate-800 p-1 rounded-lg border border-slate-700 h-[700px] relative shadow-xl">
                <div id="map" class="w-full h-full rounded z-0"></div>
                
                @if(!$pos)
                <div id="no-signal-overlay" class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center rounded">
                    <div class="text-center p-6 bg-slate-900 border border-slate-600 rounded-lg shadow-2xl">
                        <div class="text-4xl mb-2">📡</div>
                        <h3 class="text-xl font-bold text-white">Esperando señal...</h3>
                        <p class="text-slate-400 text-sm mt-2">Conectando con satélites...</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // --- 1. CONFIGURACIÓN DEL MAPA ---
    var map = L.map('map').setView([{{ $lat }}, {{ $lon }}], 15);

    // Capa OpenStreetMap (Claro / Estándar)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Icono del Vehículo
    var carIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // Variables globales para actualizar capas
    var routePolyline = null;
    var currentMarker = null;

    // --- 2. FUNCIÓN DE RASTREO (AJAX) ---
    function updateTracking() {
        fetch("{{ route('admin.gps.devices.route', $device->id) }}")
            .then(res => res.json())
            .then(data => {
                if(data.length > 0) {
                    // Ocultar overlay de "Esperando señal" si existe
                    const overlay = document.getElementById('no-signal-overlay');
                    if(overlay) overlay.style.display = 'none';

                    // 1. Extraer coordenadas para la línea [lat, lng]
                    var path = data.map(p => [p.lat, p.lng]);
                    var latest = data[0]; // La última posición (más reciente)

                    // 2. Dibujar/Actualizar la Línea de Ruta (Trazo)
                    if (routePolyline) {
                        map.removeLayer(routePolyline);
                    }
                    routePolyline = L.polyline(path, {
                        color: 'blue',
                        weight: 4,
                        opacity: 0.7,
                        dashArray: '5, 10', // Punteado
                        lineCap: 'round'
                    }).addTo(map);

                    // 3. Dibujar/Actualizar el Marcador (Auto)
                    if (currentMarker) {
                        currentMarker.setLatLng([latest.lat, latest.lng]);
                    } else {
                        currentMarker = L.marker([latest.lat, latest.lng], {icon: carIcon}).addTo(map);
                    }

                    // Actualizar Popup y Centrar
                    currentMarker.bindPopup(`
                        <b>{{ $device->name }}</b><br>
                        Velocidad: ${latest.speed} km/h<br>
                        Hora: ${latest.time}
                    `); // No abrimos popup automático para no molestar

                    // Actualizar el velocímetro en pantalla si existe el elemento
                    const speedEl = document.getElementById('disp-speed');
                    if(speedEl) speedEl.innerHTML = `${latest.speed} <span class="text-xs text-slate-500">km/h</span>`;
                }
            })
            .catch(err => console.error("Error actualizando GPS:", err));
    }

    // --- 3. INICIAR RASTREO ---
    updateTracking(); // Carga inicial inmediata
    
    // Actualizar cada 10 segundos (Tiempo real)
    setInterval(updateTracking, 10000); 


    // --- 4. COMANDOS ---
    function sendCommand(type) {
        if(!confirm('¿CONFIRMAR ACCIÓN? Se enviará el comando al dispositivo.')) return;

        // Mostrar loading o deshabilitar botones temporalmente (Opcional)
        
        fetch("{{ route('admin.gps.devices.command', $device->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json', // Importante para recibir JSON de vuelta
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type: type })
        })
        .then(async res => {
            const data = await res.json();
            if(res.ok && data.success) {
                alert('✅ Comando enviado correctamente a la cola.');
            } else {
                console.error("Error comando:", data);
                alert('❌ Error al enviar comando. Revise el log o la conexión con Traccar.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de red al conectar con el servidor.');
        });
    }
</script>
@endsection