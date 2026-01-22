@extends('layouts.admin')
@section('title', 'Crear Geocerca')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<div class="h-[calc(100vh-100px)] flex flex-col bg-slate-900 p-4">
    <div class="bg-slate-800 p-4 rounded-t-lg border border-slate-700 flex justify-between items-center">
        <h1 class="text-white font-bold text-lg">Dibujar Nueva Geocerca</h1>
        <a href="{{ route('admin.geofences.index') }}" class="text-slate-400 hover:text-white text-xs">Cancelar</a>
    </div>

    <div class="flex-1 relative bg-slate-800 border-x border-slate-700">
        <div id="map" class="w-full h-full z-0"></div>
    </div>

    <div class="bg-slate-800 p-4 rounded-b-lg border border-slate-700">
        <form action="{{ route('admin.geofences.store') }}" method="POST" class="flex gap-4 items-end">
            @csrf
            <input type="hidden" name="area_points" id="areaPoints" required>
            
            <div class="flex-1">
                <label class="block text-xs uppercase text-slate-400 mb-1">Nombre de la Zona</label>
                <input type="text" name="name" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm" placeholder="Ej: Zona Norte, Estacionamiento..." required>
            </div>
            
            <div class="flex-1">
                <label class="block text-xs uppercase text-slate-400 mb-1">Descripción</label>
                <input type="text" name="description" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm" placeholder="Opcional">
            </div>

            <button type="submit" id="btnSave" disabled class="bg-blue-600 disabled:bg-slate-600 disabled:cursor-not-allowed text-white px-6 py-2 rounded font-bold text-sm transition">
                GUARDAR GEOCERCA
            </button>
        </form>
    </div>
</div>

<script>
    // Inicializar Mapa
    var map = L.map('map').setView([10.0, -69.0], 7); // Venezuela
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    // Capa para items dibujados
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Controles de dibujo (Solo Polígonos y Círculos)
    var drawControl = new L.Control.Draw({
        draw: {
            polygon: {
                allowIntersection: false,
                showArea: true
            },
            rectangle: false,
            circle: false, // Traccar API prefiere Polígonos WKT, círculos son complejos de traducir directo a veces
            marker: false,
            polyline: false,
            circlemarker: false
        },
        edit: {
            featureGroup: drawnItems,
            remove: true
        }
    });
    map.addControl(drawControl);

    // Evento: Al terminar de dibujar
    map.on(L.Draw.Event.CREATED, function (e) {
        // Limpiar capas anteriores (Solo permitimos 1 geocerca por registro)
        drawnItems.clearLayers();
        
        var layer = e.layer;
        drawnItems.addLayer(layer);
        
        // Extraer coordenadas
        var latlngs = layer.getLatLngs()[0]; // Array de objetos {lat, lng}
        
        // Convertir a JSON simple para el input hidden
        var simplifiedPoints = latlngs.map(function(p) {
            return { lat: p.lat, lng: p.lng };
        });

        document.getElementById('areaPoints').value = JSON.stringify(simplifiedPoints);
        
        // Habilitar botón
        document.getElementById('btnSave').disabled = false;
        document.getElementById('btnSave').classList.remove('bg-slate-600');
        document.getElementById('btnSave').classList.add('hover:bg-blue-500');
    });

    map.on('draw:deleted', function () {
        document.getElementById('areaPoints').value = '';
        document.getElementById('btnSave').disabled = true;
    });
</script>
@endsection