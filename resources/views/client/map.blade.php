<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Cliente - Segusmart24</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #map { height: 100vh; width: 100%; z-index: 0; }
        .modal-overlay { background-color: rgba(0,0,0,0.5); }
    </style>
</head>
<body class="bg-gray-900 text-white overflow-hidden">

    <div class="absolute top-4 left-4 z-10 bg-gray-800 p-4 rounded-lg shadow-lg opacity-90">
        <h1 class="font-bold text-xl text-blue-500">SEGUSMART<span class="text-white">24</span></h1>
        <p class="text-xs text-gray-400">Bienvenido, {{ Auth::user()->name }}</p>
        <div class="mt-4 flex gap-2">
            <button onclick="loadModal('/modal/billing')" class="bg-green-600 px-3 py-1 text-sm rounded hover:bg-green-500">💰 Facturación</button>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 px-3 py-1 text-sm rounded hover:bg-red-500">Salir</button>
            </form>
        </div>
    </div>

    <div id="map"></div>

    <div id="modal" class="fixed inset-0 z-50 hidden flex items-center justify-center modal-overlay">
        <div class="bg-white text-black p-6 rounded-lg shadow-2xl w-full max-w-lg relative">
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
            <div id="modal-content">
                Cargando...
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 1. Inicializar Mapa
        var map = L.map('map').setView([10.4806, -66.9036], 6); // Centrado en Vzla
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: 'Segusmart24'
        }).addTo(map);

        // 2. Cargar Activos
        fetch('{{ route("client.api.assets") }}')
            .then(res => res.json())
            .then(data => {
                data.assets.forEach(asset => {
                    let color = asset.type === 'alarm' ? 'blue' : 'orange';
                    let iconHtml = asset.type === 'alarm' ? '🏠' : '🚗';
                    
                    // Marcador Simple
                    let marker = L.marker([asset.lat, asset.lng]).addTo(map);
                    marker.bindTooltip(asset.name);
                    
                    // Al hacer click, abrir modal
                    marker.on('click', () => {
                        loadModal(`/portal/modal/${asset.type}/${asset.id}`);
                    });
                });
            });

        // Funciones del Modal
        function loadModal(url) {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modal-content').innerHTML = '<p class="text-center">Cargando datos...</p>';
            
            fetch(url) // Debes definir estas rutas en web.php
                .then(res => res.text())
                .then(html => {
                    document.getElementById('modal-content').innerHTML = html;
                });
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }
    </script>
</body>
</html>