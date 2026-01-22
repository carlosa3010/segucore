@extends('layouts.admin')
@section('title', 'Monitor GPS')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@php
    // Preparar datos
    $pos = $liveData ? $liveData->position : null;
    $lat = $pos ? $pos->latitude : 10.0; // Default Vzla
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
                        <span class="block text-2xl font-bold text-blue-400">{{ $speedKmh }} <span class="text-xs text-slate-500">km/h</span></span>
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
                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center rounded">
                    <div class="text-center p-6 bg-slate-900 border border-slate-600 rounded-lg shadow-2xl">
                        <div class="text-4xl mb-2">📡</div>
                        <h3 class="text-xl font-bold text-white">Esperando primera señal...</h3>
                        <p class="text-slate-400 text-sm mt-2">El dispositivo aún no ha reportado ubicación al servidor.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    @if($pos)
        // --- CASO 1: HAY SEÑAL GPS ---
        // Coordenadas reales
        const lat = {{ $lat }};
        const lon = {{ $lon }};
        const name = "{{ $device->name }}";
        const speed = "{{ $speedKmh }} km/h";

        var map = L.map('map').setView([lat, lon], 15);

        // CAPA DE MAPA: OpenStreetMap (Claro)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Icono personalizado Azul
        var carIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Marcador con Popup
        L.marker([lat, lon], {icon: carIcon}).addTo(map)
            .bindPopup(`<b>${name}</b><br>Velocidad: ${speed}<br>Lat: ${lat}<br>Lon: ${lon}`)
            .openPopup();
    @else
        // --- CASO 2: SIN SEÑAL (Mapa por defecto) ---
        var map = L.map('map').setView([10.0, -69.0], 6); // Centrado en Vzla
        
        // CAPA DE MAPA: OpenStreetMap (Claro)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    @endif

    // Lógica para enviar comandos (Engine Stop / Resume)
    function sendCommand(type) {
        if(!confirm('¿CONFIRMAR ACCIÓN? Se enviará el comando al dispositivo.')) return;

        fetch("{{ route('admin.gps.devices.command', $device->id) }}", {
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
                alert('✅ Comando enviado correctamente a Traccar API.');
            } else {
                alert('❌ Error al enviar comando. Verifique conexión.');
            }
        })
        .catch(err => alert('Error de red al conectar con el servidor.'));
    }
</script>
@endsection