@extends('layouts.admin')
@section('title', 'Editar Geocerca')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<div class="h-[calc(100vh-100px)] flex flex-col bg-slate-900 p-4">
    <div class="bg-slate-800 p-4 rounded-t-lg border border-slate-700 flex justify-between items-center">
        <h1 class="text-white font-bold text-lg">✏️ Editar Geocerca: {{ $geofence->name }}</h1>
        <a href="{{ route('admin.geofences.index') }}" class="text-slate-400 hover:text-white text-xs">Cancelar</a>
    </div>

    <div class="flex-1 relative bg-slate-800 border-x border-slate-700">
        <div id="map" class="w-full h-full z-0"></div>
    </div>

    <div class="bg-slate-800 p-4 rounded-b-lg border border-slate-700">
        <form action="{{ route('admin.geofences.update', $geofence->id) }}" method="POST" class="flex gap-4 items-end">
            @csrf
            @method('PUT')
            <input type="hidden" name="area_points" id="areaPoints" required>
            
            <div class="flex-1">
                <label class="block text-xs uppercase text-slate-400 mb-1">Nombre de la Zona</label>
                <input type="text" name="name" value="{{ $geofence->name }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm" required>
            </div>
            
            <div class="flex-1">
                <label class="block text-xs uppercase text-slate-400 mb-1">Descripción</label>
                <input type="text" name="description" value="{{ $geofence->description }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white text-sm">
            </div>

            <button type="submit" id="btnSave" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold text-sm transition">
                ACTUALIZAR ZONA
            </button>
        </form>
    </div>
</div>

<script>
    // Inicializar Mapa
    var map = L.map('map').setView([10.0, -69.0], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    // Capa para items dibujados
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // 1. CARGAR GEOCERCA EXISTENTE
    var existingPolygon = @json($polygon);
    
    if (existingPolygon.length > 0) {
        // Convertir formato [{lat,lng}] a Array de Arrays [[lat,lng]] para Leaflet
        var latlngs = existingPolygon.map(p => [p.lat, p.lng]);
        
        var polyLayer = L.polygon(latlngs, {color: '#3b82f6'}).addTo(drawnItems);
        map.fitBounds(polyLayer.getBounds());
        
        // Llenar el input oculto inicialmente por si no editan el mapa
        updateHiddenInput(polyLayer);
    }

    // Controles de dibujo
    var drawControl = new L.Control.Draw({
        draw: {
            polygon: { allowIntersection: false, showArea: true },
            rectangle: false,
            circle: false,
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

    // Evento: Al CREAR una nueva (si borraron la anterior)
    map.on(L.Draw.Event.CREATED, function (e) {
        drawnItems.clearLayers(); // Solo 1 a la vez
        var layer = e.layer;
        drawnItems.addLayer(layer);
        updateHiddenInput(layer);
    });

    // Evento: Al EDITAR vértices
    map.on(L.Draw.Event.EDITED, function (e) {
        var layers = e.layers;
        layers.eachLayer(function (layer) {
            updateHiddenInput(layer);
        });
    });

    // Evento: Al BORRAR
    map.on(L.Draw.Event.DELETED, function () {
        document.getElementById('areaPoints').value = '';
    });

    // Función auxiliar para guardar coordenadas
    function updateHiddenInput(layer) {
        var latlngs = layer.getLatLngs()[0]; // Obtener puntos del polígono
        
        // A veces Leaflet devuelve estructuras anidadas complejas, aseguramos un array plano
        if(Array.isArray(latlngs[0])) { latlngs = latlngs[0]; }

        var simplifiedPoints = latlngs.map(function(p) {
            return { lat: p.lat, lng: p.lng };
        });

        document.getElementById('areaPoints').value = JSON.stringify(simplifiedPoints);
    }
</script>
@endsection