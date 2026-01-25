<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Cliente - Segusmart24</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; }
        #map { height: 100vh; width: 100%; z-index: 0; }
        
        /* Sidebar Glass Effect */
        .sidebar-panel { 
            background: rgba(15, 23, 42, 0.95); 
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        /* Marcadores con rotación */
        .gps-arrow {
            transition: transform 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }

        /* Animaciones */
        .slide-in { animation: slideIn 0.3s ease-out forwards; }
        @keyframes slideIn { from { transform: translateX(-100%); } to { transform: translateX(0); } }
    </style>
</head>
<body class="bg-gray-100 overflow-hidden flex">

    <div class="w-96 h-full sidebar-panel z-20 flex flex-col shadow-2xl relative transition-all duration-300" id="sidebar">
        
        <div class="p-6 border-b border-gray-700 flex flex-col items-center justify-center bg-black/20">
            <img src="{{ asset('images/logo-white.png') }}" alt="SeguCore" class="h-10 mb-4 object-contain">
            
            <div class="relative w-full mt-2">
                <input type="text" id="searchInput" placeholder="Buscar dispositivo..." 
                       class="w-full bg-gray-800 text-gray-200 text-sm rounded-lg pl-10 pr-4 py-2 border border-gray-700 focus:outline-none focus:border-blue-500 transition">
                <i class="fas fa-search absolute left-3 top-3 text-gray-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 p-2 bg-gray-800/50">
            <button onclick="toggleAlertsPanel()" class="bg-gray-700 hover:bg-gray-600 text-gray-300 py-2 rounded text-xs font-bold flex items-center justify-center gap-2 relative">
                <i class="fas fa-bell"></i> ALERTAS
                <span id="alert-badge" class="hidden absolute top-1 right-2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
            </button>
            <button onclick="loadModal('{{ route('client.modal.billing') }}')" class="bg-gray-700 hover:bg-gray-600 text-gray-300 py-2 rounded text-xs font-bold flex items-center justify-center gap-2">
                <i class="fas fa-file-invoice-dollar"></i> FACTURAS
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-4" id="assets-list">
            <div class="text-center mt-10 text-gray-500 animate-pulse">
                <i class="fas fa-satellite-dish fa-spin text-2xl mb-2"></i><br>Conectando con satélites...
            </div>
        </div>

        <div class="p-4 border-t border-gray-700 bg-gray-900/80">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-blue-400 flex items-center justify-center font-bold text-white shadow-lg">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="font-bold text-white text-sm truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-green-400 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Conectado</p>
                </div>
            </div>
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button type="submit" class="w-full text-red-400 hover:text-white hover:bg-red-600/20 text-xs py-2 rounded transition border border-transparent hover:border-red-500/30">
                    <i class="fas fa-power-off"></i> Cerrar Sesión
                </button>
            </form>
        </div>
        
        <button onclick="toggleSidebar()" class="absolute -right-8 top-1/2 bg-gray-800 text-white p-3 rounded-r-lg shadow-lg md:hidden hover:bg-blue-600 transition">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div id="alerts-panel" class="fixed left-96 top-0 bottom-0 w-80 bg-gray-900/95 backdrop-blur z-10 transform -translate-x-full transition-transform duration-300 border-r border-gray-700 flex flex-col">
        <div class="p-4 border-b border-gray-700 flex justify-between items-center bg-red-900/20">
            <h3 class="font-bold text-red-400"><i class="fas fa-exclamation-triangle"></i> Últimas Alertas</h3>
            <button onclick="toggleAlertsPanel()" class="text-gray-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div id="alerts-list" class="flex-1 overflow-y-auto p-2 space-y-2">
            </div>
    </div>

    <div class="flex-1 relative">
        <div id="map"></div>
        
        <div id="history-control" class="hidden absolute top-4 right-4 z-[400] bg-white p-3 rounded shadow-lg max-w-sm">
            <div class="flex justify-between items-center mb-2">
                <h4 class="font-bold text-gray-700 text-sm">Reproduciendo Historial</h4>
                <button onclick="clearHistory()" class="text-red-500 hover:text-red-700 text-xs font-bold">CERRAR</button>
            </div>
            <p id="history-info" class="text-xs text-gray-500 mb-2">Cargando datos...</p>
            <div class="flex gap-2">
                <button onclick="playHistory()" class="bg-blue-600 text-white px-3 py-1 rounded text-xs"><i class="fas fa-play"></i></button>
                <button onclick="pauseHistory()" class="bg-gray-500 text-white px-3 py-1 rounded text-xs"><i class="fas fa-pause"></i></button>
                <input type="range" id="history-slider" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer mt-1">
            </div>
        </div>
    </div>

    <div id="modal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-gray-800 text-white w-full max-w-2xl rounded-xl shadow-2xl border border-gray-600 transform transition-all scale-95 opacity-0 flex flex-col max-h-[90vh]" id="modal-container">
            <div class="flex justify-between items-center p-4 border-b border-gray-700 bg-gray-900/50 rounded-t-xl">
                <h3 class="font-bold text-lg text-blue-400" id="modal-title">Detalle</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition text-2xl leading-none">&times;</button>
            </div>
            <div id="modal-content" class="p-6 overflow-y-auto custom-scrollbar"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <script>
        // --- 1. CONFIGURACIÓN DEL MAPA (Claro) ---
        var map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        
        // Capa Clara (CartoDB Positron)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© Segusmart24',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);
        
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Variables Globales
        var markers = {}; // Diccionario de marcadores {id: marker}
        var historyPolyline = null;
        var historyMarker = null;
        var historyData = [];
        var historyAnimation = null;
        var assetsData = []; // Copia local para búsqueda

        // --- 2. GESTIÓN DE ACTIVOS (Polling) ---
        function fetchAssets() {
            fetch('{{ route("client.api.assets") }}')
                .then(res => res.json())
                .then(data => {
                    assetsData = data.assets;
                    renderAssetsList(assetsData); // Actualizar lista lateral
                    updateMapMarkers(assetsData); // Actualizar mapa
                });
        }

        // Bucle de actualización (cada 10 seg)
        setInterval(fetchAssets, 10000);
        fetchAssets(); // Primer carga
        fetchAlerts(); // Cargar alertas

        // --- 3. RENDERIZADO LATERAL ---
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const filtered = assetsData.filter(a => a.name.toLowerCase().includes(term) || (a.plate && a.plate.toLowerCase().includes(term)));
            renderAssetsList(filtered);
        });

        function renderAssetsList(assets) {
            const container = document.getElementById('assets-list');
            container.innerHTML = '';

            if (assets.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 text-xs p-4">No se encontraron resultados</div>';
                return;
            }

            assets.forEach(asset => {
                const isOnline = asset.status === 'online' || asset.status === 'armed';
                const statusColor = isOnline ? 'text-green-400' : 'text-gray-500';
                const icon = asset.type === 'gps' ? 'fa-car-side' : 'fa-house-signal';
                
                let details = asset.type === 'gps' 
                    ? `<span class="text-[10px] bg-gray-700 px-1 rounded text-gray-300 mr-2"><i class="fas fa-tachometer-alt"></i> ${asset.speed} km/h</span>` 
                    : '';

                const item = document.createElement('div');
                item.className = "bg-gray-800/40 p-3 rounded border border-gray-700 hover:bg-gray-700/50 cursor-pointer transition group";
                item.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="${statusColor} text-lg w-8 text-center"><i class="fas ${icon}"></i></div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-gray-200 group-hover:text-blue-400 truncate transition">${asset.name}</h4>
                                <div class="flex items-center mt-1">
                                    ${details}
                                    <span class="text-[10px] text-gray-500">${asset.last_update}</span>
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-600 text-xs"></i>
                    </div>
                `;
                item.onclick = () => focusAsset(asset);
                container.appendChild(item);
            });
        }

        // --- 4. LOGICA DEL MAPA Y MARCADORES ---
        function updateMapMarkers(assets) {
            assets.forEach(asset => {
                // Definir Icono
                let color = (asset.status === 'online' || asset.status === 'armed') ? '#10B981' : '#6B7280';
                if(asset.type === 'gps' && asset.speed > 0) color = '#3B82F6'; // Azul si se mueve
                if(asset.type === 'alarm' && asset.status === 'disarmed') color = '#EF4444';

                let iconHtml = asset.type === 'gps'
                    ? `<div class="gps-arrow" style="transform: rotate(${asset.course}deg); color: ${color}; font-size: 24px; text-shadow: 1px 1px 2px white;"><i class="fas fa-location-arrow"></i></div>`
                    : `<div style="color: ${color}; font-size: 28px; text-shadow: 1px 1px 2px white;"><i class="fas fa-shield-alt"></i></div>`;

                let customIcon = L.divIcon({
                    className: 'custom-map-icon',
                    html: iconHtml,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });

                if (markers[asset.id]) {
                    // Actualizar existente (Animar movimiento)
                    markers[asset.id].setIcon(customIcon);
                    markers[asset.id].setLatLng([asset.lat, asset.lng]);
                } else {
                    // Crear nuevo
                    let marker = L.marker([asset.lat, asset.lng], {icon: customIcon}).addTo(map);
                    marker.on('click', () => loadModal(`/portal/modal/${asset.type}/${asset.id}`));
                    // Tooltip simple
                    marker.bindTooltip(asset.name, {direction: 'top', offset: [0, -10]});
                    markers[asset.id] = marker;
                }
            });
        }

        function focusAsset(asset) {
            map.flyTo([asset.lat, asset.lng], 16);
            if(window.innerWidth < 768) toggleSidebar(); // Cerrar sidebar en movil
            // Opcional: Abrir modal automáticamente
            // loadModal(`/portal/modal/${asset.type}/${asset.id}`);
        }

        // --- 5. ALERTAS ---
        function fetchAlerts() {
            fetch('{{ route("client.api.alerts") }}')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('alerts-list');
                    const badge = document.getElementById('alert-badge');
                    list.innerHTML = '';
                    
                    if(data.length > 0) {
                        badge.classList.remove('hidden');
                        data.forEach(alert => {
                            list.innerHTML += `
                                <div class="bg-gray-800 p-3 rounded border-l-4 border-red-500 mb-2">
                                    <p class="text-xs text-red-400 font-bold">${alert.device}</p>
                                    <p class="text-sm text-gray-200">${alert.message}</p>
                                    <p class="text-[10px] text-gray-500 text-right mt-1">${alert.time}</p>
                                </div>
                            `;
                        });
                    } else {
                        badge.classList.add('hidden');
                        list.innerHTML = '<p class="text-gray-500 text-center text-sm p-4">Sin alertas recientes.</p>';
                    }
                });
        }

        function toggleAlertsPanel() {
            const panel = document.getElementById('alerts-panel');
            if(panel.classList.contains('-translate-x-full')) {
                panel.classList.remove('-translate-x-full');
            } else {
                panel.classList.add('-translate-x-full');
            }
        }

        // --- 6. HISTORIAL DE RECORRIDO ---
        window.loadHistory = function(deviceId, startStr, endStr) {
            closeModal();
            document.getElementById('history-control').classList.remove('hidden');
            document.getElementById('history-info').innerText = 'Descargando datos...';

            // Limpiar previo
            if(historyPolyline) map.removeLayer(historyPolyline);
            if(historyMarker) map.removeLayer(historyMarker);

            fetch(`/portal/api/history/${deviceId}?start=${startStr}&end=${endStr}`)
                .then(res => res.json())
                .then(data => {
                    if(data.error) { alert(data.error); return; }
                    historyData = data.positions;

                    if(historyData.length === 0) {
                        alert("No hay datos de recorrido en este rango.");
                        clearHistory();
                        return;
                    }

                    // Dibujar Ruta
                    const latlngs = historyData.map(p => [p.latitude, p.longitude]);
                    historyPolyline = L.polyline(latlngs, {color: 'blue', weight: 4}).addTo(map);
                    map.fitBounds(historyPolyline.getBounds());

                    // Configurar Slider
                    const slider = document.getElementById('history-slider');
                    slider.max = historyData.length - 1;
                    slider.value = 0;
                    slider.oninput = (e) => moveHistoryMarker(e.target.value);

                    // Crear marcador de historial
                    historyMarker = L.marker(latlngs[0], {
                        icon: L.divIcon({html: '<i class="fas fa-car text-blue-600 text-2xl"></i>', className: '', iconSize:[20,20]})
                    }).addTo(map);

                    document.getElementById('history-info').innerText = `${historyData.length} puntos encontrados.`;
                });
        };

        function moveHistoryMarker(index) {
            if(!historyData[index]) return;
            const pos = historyData[index];
            historyMarker.setLatLng([pos.latitude, pos.longitude]);
            document.getElementById('history-info').innerText = `Vel: ${Math.round(pos.speed)} km/h - ${new Date(pos.device_time).toLocaleTimeString()}`;
        }

        function clearHistory() {
            if(historyPolyline) map.removeLayer(historyPolyline);
            if(historyMarker) map.removeLayer(historyMarker);
            document.getElementById('history-control').classList.add('hidden');
        }

        // --- MODAL Y UI ---
        function loadModal(url) {
            const modal = document.getElementById('modal');
            const container = document.getElementById('modal-container');
            const content = document.getElementById('modal-content');
            
            modal.classList.remove('hidden');
            setTimeout(() => { 
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);

            content.innerHTML = '<div class="text-center py-10"><i class="fas fa-circle-notch fa-spin text-4xl text-blue-500"></i></div>';

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    content.innerHTML = html;
                    // Inicializar datepickers si existen en el modal cargado
                    if(document.querySelector('.datepicker')) {
                        flatpickr(".datepicker", { 
                            enableTime: true, 
                            dateFormat: "Y-m-d H:i",
                            locale: "es",
                            theme: "dark"
                        });
                    }
                });
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            const container = document.getElementById('modal-container');
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>