<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Segusmart24</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #map { height: 100vh; width: 100%; z-index: 0; }
        .glass-panel { background: rgba(31, 41, 55, 0.95); backdrop-filter: blur(10px); }
        /* Scrollbar personalizada */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        .marker-icon { text-align: center; color: white; font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); }
    </style>
</head>
<body class="bg-gray-900 text-white overflow-hidden flex">

    <div class="w-80 h-full glass-panel z-20 flex flex-col border-r border-gray-700 shadow-2xl relative transition-transform duration-300 transform" id="sidebar">
        
        <div class="p-6 border-b border-gray-700">
            <h1 class="font-bold text-2xl text-blue-500 tracking-tighter">SEGUSMART<span class="text-white">24</span></h1>
            <div class="mt-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-lg font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-semibold text-sm">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400">Cliente Verificado</p>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-6" id="assets-list">
            <p class="text-center text-gray-500 mt-10"><i class="fas fa-spinner fa-spin"></i> Cargando activos...</p>
        </div>

        <div class="p-4 border-t border-gray-700 bg-gray-800/50">
            <button onclick="loadModal('{{ route('client.modal.billing') }}')" class="w-full mb-3 bg-green-600/20 text-green-400 border border-green-600/50 hover:bg-green-600 hover:text-white py-2 rounded transition flex items-center justify-center gap-2">
                <i class="fas fa-file-invoice-dollar"></i> Ver Facturación
            </button>
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button type="submit" class="w-full text-red-400 hover:text-white text-sm py-2 hover:bg-red-600/20 rounded transition">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>
        
        <button onclick="toggleSidebar()" class="absolute -right-8 top-1/2 bg-gray-800 text-white p-2 rounded-r-lg shadow-lg md:hidden">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="flex-1 relative">
        <div id="map"></div>
    </div>

    <div id="modal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-gray-800 text-white w-full max-w-lg rounded-xl shadow-2xl border border-gray-600 transform transition-all scale-95 opacity-0" id="modal-container">
            <div class="flex justify-between items-center p-4 border-b border-gray-700">
                <h3 class="font-bold text-lg" id="modal-title">Detalle</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
            </div>
            <div id="modal-content" class="p-6 max-h-[70vh] overflow-y-auto">
                </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 1. Configuración del Mapa
        var map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© Segusmart24',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);
        L.control.zoom({ position: 'topright' }).addTo(map);

        var markers = {}; // Guardar referencias a marcadores

        // 2. Cargar Datos
        fetch('{{ route("client.api.assets") }}')
            .then(res => res.json())
            .then(data => {
                const listContainer = document.getElementById('assets-list');
                listContainer.innerHTML = ''; 

                if(data.assets.length === 0) {
                    listContainer.innerHTML = '<div class="p-4 text-center text-gray-400 bg-gray-800 rounded">No se encontraron servicios activos para su cuenta.</div>';
                    // Centrar mapa por defecto si no hay datos
                    map.setView([10.4806, -66.9036], 6); 
                    return;
                }

                // Separar por tipo para el sidebar
                const alarms = data.assets.filter(a => a.type === 'alarm');
                const gps = data.assets.filter(a => a.type === 'gps');

                // Renderizar Alarmas
                if(alarms.length > 0) {
                    listContainer.innerHTML += `<h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Monitoreo (${alarms.length})</h3>`;
                    alarms.forEach(asset => renderAsset(asset, listContainer));
                }

                // Renderizar GPS
                if(gps.length > 0) {
                    listContainer.innerHTML += `<h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-6 mb-2">Rastreo GPS (${gps.length})</h3>`;
                    gps.forEach(asset => renderAsset(asset, listContainer));
                }

                // Ajustar mapa para ver todos los puntos
                if (data.assets.length > 0) {
                    var group = new L.featureGroup(Object.values(markers));
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            });

        function renderAsset(asset, container) {
            // Icono según estado
            let iconColor = asset.status === 'armed' || asset.status === 'online' ? 'text-green-500' : 'text-red-500';
            let iconClass = asset.type === 'alarm' ? 'fa-home' : 'fa-car';
            let statusText = asset.type === 'alarm' ? (asset.status === 'armed' ? 'Armado' : 'Desarmado') : (asset.status === 'online' ? 'En Movimiento' : 'Detenido');

            // 1. Agregar al Mapa
            let customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style='font-size: 24px; color: ${asset.status === 'online' || asset.status === 'armed' ? '#10B981' : '#EF4444'}; text-shadow: 0 0 10px black;'><i class="fas ${iconClass}"></i></div>`,
                iconSize: [30, 42],
                iconAnchor: [15, 42]
            });

            let marker = L.marker([asset.lat, asset.lng], {icon: customIcon}).addTo(map);
            marker.on('click', () => loadModal(`/portal/modal/${asset.type}/${asset.id}`));
            markers[asset.id] = marker;

            // 2. Agregar a la Lista Lateral
            let item = document.createElement('div');
            item.className = "bg-gray-800 p-4 rounded-lg cursor-pointer hover:bg-gray-700 transition border-l-4 border-transparent hover:border-blue-500 group";
            item.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="${iconColor} bg-gray-900 p-2 rounded-full"><i class="fas ${iconClass}"></i></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-200 group-hover:text-white">${asset.name}</h4>
                            <p class="text-xs text-gray-400">${statusText}</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </div>
            `;
            item.onclick = () => {
                map.flyTo([asset.lat, asset.lng], 16);
                loadModal(`/portal/modal/${asset.type}/${asset.id}`);
            };
            container.appendChild(item);
        }

        // --- Funciones del Modal ---
        function loadModal(url) {
            const modal = document.getElementById('modal');
            const container = document.getElementById('modal-container');
            const content = document.getElementById('modal-content');
            
            modal.classList.remove('hidden');
            setTimeout(() => { // Animación de entrada
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);

            content.innerHTML = '<div class="text-center py-10"><i class="fas fa-circle-notch fa-spin text-4xl text-blue-500"></i></div>';

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    content.innerHTML = html;
                });
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            const container = document.getElementById('modal-container');
            
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>