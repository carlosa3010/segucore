@extends('layouts.admin')
@section('title', 'Detalle GPS')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="bg-slate-900 min-h-screen p-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
                <h2 class="text-xl font-bold text-white mb-4">{{ $device->name }}</h2>
                
                <div class="space-y-3 text-sm text-slate-300">
                    <div class="flex justify-between">
                        <span>IMEI:</span> <span class="font-mono text-yellow-500">{{ $device->unique_id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Placa:</span> <span class="font-bold">{{ $device->plate_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Estado:</span> 
                        @if($liveData && $liveData->status == 'online')
                            <span class="text-green-400 font-bold">ONLINE</span>
                        @else
                            <span class="text-slate-500">OFFLINE</span>
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span>Última señal:</span>
                        <span>{{ $liveData ? \Carbon\Carbon::parse($liveData->lastupdate)->diffForHumans() : 'Nunca' }}</span>
                    </div>
                </div>

                @if($device->vehicle_type !== 'person')
                <div class="mt-6 pt-6 border-t border-slate-700">
                    <h3 class="font-bold text-white mb-3">Comandos Remotos</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="sendCommand('engineStop')" class="bg-red-900/50 hover:bg-red-600 text-red-200 hover:text-white border border-red-800 py-2 rounded text-xs transition">
                            🛑 APAGAR MOTOR
                        </button>
                        <button onclick="sendCommand('engineResume')" class="bg-green-900/50 hover:bg-green-600 text-green-200 hover:text-white border border-green-800 py-2 rounded text-xs transition">
                            ⚡ HABILITAR MOTOR
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-slate-800 p-1 rounded-lg border border-slate-700 h-[600px] relative">
                <div id="map" class="w-full h-full rounded"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializar Mapa
    var map = L.map('map').setView([{{ $liveData && $liveData->positionid ? 10.0 : 10.0 }}, -69.0], 13);

    // --- OPCIONES DE CAPAS DE MAPA (Descomenta la que prefieras) ---

    // OPCIÓN 1: Google Maps Híbrido (Satélite + Calles) - RECOMENDADO
    L.tileLayer('http://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(map);

    // OPCIÓN 2: Google Maps Calles (Clásico Claro)
    /*
    L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(map);
    */

    // OPCIÓN 3: OpenStreetMap (Estándar gratuito)
    /*
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    */

    // -------------------------------------------------------------

    @if($liveData && $liveData->positionid)
        // Lógica para marcador si existe posición...
        // Nota: Como mencionamos antes, aquí idealmente deberías pasar lat/lon reales
        // desde el controlador (usando la relación position que creamos).
        // Por ahora mantengo tu lógica original de coordenadas fijas o variables si las pasas.
    @endif

    function sendCommand(type) {
        if(!confirm('¿Estás seguro de enviar este comando?')) return;

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
            if(data.success) alert('Comando enviado con éxito');
            else alert('Error al enviar comando');
        });
    }
</script>
@endsection