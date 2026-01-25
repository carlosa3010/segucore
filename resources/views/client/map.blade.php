<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SeguCore Cliente</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background: #000; color: #e5e7eb; }
        #map { height: 100vh; width: 100%; z-index: 1; }
        .sidebar-container { display: flex; flex-direction: column; height: 100vh; background: #000; border-right: 1px solid #222; }
        .sidebar-content { flex: 1; overflow-y: auto; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }
        #alerts-panel { box-shadow: -5px 0 20px rgba(0,0,0,0.9); border-left: 1px solid #222; }
    </style>
</head>
<body class="overflow-hidden flex bg-black">

    <div class="w-80 sidebar-container z-20 relative transition-transform duration-300" id="sidebar">
        <div class="p-6 border-b border-gray-800 bg-black flex justify-center">
            <img src="{{ asset('images/logo-white.png') }}" alt="SeguCore" class="h-10 object-contain opacity-90">
        </div>

        <div class="p-4 border-b border-gray-800">
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Buscar activo..." class="w-full bg-gray-900 border border-gray-700 text-sm rounded px-3 py-2 pl-9 text-gray-200 focus:outline-none focus:border-gray-500 transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-2 border-b border-gray-800 bg-gray-900/30">
            <button onclick="toggleAlerts()" class="py-3 text-xs font-bold text-gray-400 hover:text-white hover:bg-gray-800 transition border-r border-gray-800 relative">
                <i class="fas fa-bell mr-1"></i> ALERTAS
                <span id="alert-badge" class="hidden absolute top-2 right-6 w-2 h-2 bg-red-600 rounded-full animate-pulse"></span>
            </button>
            <button onclick="loadModal('{{ route('client.modal.billing') }}')" class="py-3 text-xs font-bold text-gray-400 hover:text-white hover:bg-gray-800 transition">
                <i class="fas fa-file-invoice mr-1"></i> FACTURAS
            </button>
        </div>

        <div class="sidebar-content p-2 space-y-2" id="assets-list">
            <div class="text-center mt-10 text-gray-600 text-xs"><i class="fas fa-circle-notch fa-spin"></i> Cargando...</div>
        </div>

        <div class="p-4 border-t border-gray-800 bg-black">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded bg-gray-800 flex items-center justify-center font-bold text-white text-sm border border-gray-700">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="font-bold text-white text-sm truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Conectado</p>
                </div>
            </div>
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button type="submit" class="w-full bg-gray-900 hover:bg-red-900/20 text-gray-400 hover:text-red-400 text-xs font-bold py-2 rounded border border-gray-800 hover:border-red-900/50 transition">
                    <i class="fas fa-power-off mr-2"></i> CERRAR SESIÓN
                </button>
            </form>
        </div>
    </div>

    <div id="alerts-panel" class="fixed right-0 top-0 bottom-0 w-80 z-30 bg-black/95 backdrop-blur transform translate-x-full transition-transform duration-300 flex flex-col">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-900">
            <h3 class="font-bold text-sm text-gray-200 uppercase">Notificaciones</h3>
            <button onclick="toggleAlerts()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div id="alerts-content" class="flex-1 overflow-y-auto p-4 space-y-3"></div>
    </div>

    <div class="flex-1 relative bg-gray-900">
        <div id="map"></div>
        
        <button id="close-history-btn" onclick="clearHistory()" class="hidden absolute top-4 right-4 z-[400] bg-white text-black font-bold px-4 py-2 rounded shadow-lg hover:bg-gray-200 text-xs flex items-center gap-2">
            <i class="fas fa-times text-red-500"></i> LIMPIAR RUTA
        </button>

        <div id="history-legend" class="hidden absolute bottom-8 right-4 z-[400] bg-gray-900/90 p-3 rounded-lg border border-gray-700 text-xs text-gray-300 shadow-2xl backdrop-blur w-40">
            <h5 class="font-bold text-white mb-2 border-b border-gray-700 pb-1 text-center uppercase tracking-wider">Leyenda</h5>
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-black border border-gray-500 shadow shadow-white/10"></span> 
                    <span>Detenido (0 km)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-1.5 rounded-full bg-green-500"></span> 
                    <span>1 - 40 km/h</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-1.5 rounded-full bg-yellow-500"></span> 
                    <span>40 - 80 km/h</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-1.5 rounded-full bg-red-600"></span> 
                    <span>+80 km/h</span>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-overlay" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
        <div id="modal-content" class="bg-gray-900 border border-gray-700 w-full max-w-2xl rounded-lg shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-200 min-h-[200px]">
            </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        const map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        // Mapa base claro para buen contraste con las rutas de colores
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { attribution: '', maxZoom: 19 }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        let markers = {}; 
        let historyLayer = L.layerGroup().addTo(map); 

        // --- FUNCIONES GLOBALES ---

        // 1. Enviar Comando
        window.sendCommand = function(deviceId, type) {
            if(!confirm('¿ATENCIÓN: Está seguro de enviar este comando al vehículo?')) return;
            const feedback = document.getElementById('command-feedback');
            feedback.innerHTML = '<span class="text-blue-400 animate-pulse"><i class="fas fa-satellite-dish"></i> Enviando...</span>';

            fetch(`/api/device/${deviceId}/command`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ type: type })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    feedback.innerHTML = '<span class="text-green-500 font-bold"><i class="fas fa-check"></i> ' + data.message + '</span>';
                } else {
                    feedback.innerHTML = '<span class="text-red-500 font-bold"><i class="fas fa-times"></i> ' + (data.message || 'Error') + '</span>';
                }
            })
            .catch(err => {
                feedback.innerHTML = '<span class="text-red-500"><i class="fas fa-wifi"></i> Error de conexión.</span>';
            });
        };

        // 2. Consultar Historial
        window.fetchHistory = function(deviceId) {
            const btn = document.getElementById('btn-history');
            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> CALCULANDO RUTA...';

            fetch(`/api/history/${deviceId}?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`)
                .then(res => res.json())
                .then(data => {
                    if(data.error) {
                        alert(data.error);
                    } else if(!data.positions || data.positions.length === 0) {
                        alert("No se encontraron datos de recorrido en este rango.");
                    } else {
                        closeModal();
                        drawHistoryAdvanced(data.positions);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error obteniendo el historial.");
                })
                .finally(() => {
                    if(btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-search-location"></i> CONSULTAR RUTA';
                    }
                });
        };

        // 3. DIBUJAR HISTORIAL MULTICOLOR
        function drawHistoryAdvanced(positions) {
            clearHistory(); // Limpiar mapa previo
            
            if(positions.length < 2) return;

            // Ajustar zoom a toda la ruta
            const allPoints = positions.map(p => [p.latitude, p.longitude]);
            map.fitBounds(L.polyline(allPoints).getBounds(), {padding: [50, 50]});

            // Iterar punto a punto para colorear segmentos
            for (let i = 0; i < positions.length - 1; i++) {
                let p1 = positions[i];
                let p2 = positions[i+1];
                
                // Lógica de colores según velocidad
                let color = '#3B82F6'; // Default
                let speed = p1.speed;

                if (speed === 0) color = '#000000';       // Detenido (Negro)
                else if (speed < 40) color = '#10B981';   // Lento (Verde)
                else if (speed < 80) color = '#EAB308';   // Normal (Amarillo)
                else color = '#EF4444';                   // Rápido (Rojo)

                // Dibujar línea entre punto A y B
                L.polyline([[p1.latitude, p1.longitude], [p2.latitude, p2.longitude]], {
                    color: color, 
                    weight: 5, 
                    opacity: 0.8,
                    lineCap: 'round'
                }).addTo(historyLayer).bindTooltip(
                    `<div class="text-center font-sans">
                        <b>${new Date(p1.device_time).toLocaleTimeString()}</b><br>
                        Vel: ${speed} km/h
                     </div>`, 
                    { sticky: true, direction: 'top', className: 'bg-black text-white border-0' }
                );

                // Si está detenido, agregar un punto marcador para que se note
                if (speed === 0) {
                    L.circleMarker([p1.latitude, p1.longitude], {
                        radius: 3,
                        color: '#000',
                        fillColor: '#000',
                        fillOpacity: 1
                    }).addTo(historyLayer);
                }
            }

            // Marcadores de Inicio (Play) y Fin (Bandera)
            const startPos = positions[0];
            const endPos = positions[positions.length - 1];

            L.marker([startPos.latitude, startPos.longitude], {
                icon: L.divIcon({
                    html: '<div class="bg-green-500 text-white rounded-full p-1 w-8 h-8 flex items-center justify-center shadow-lg border-2 border-white"><i class="fas fa-play ml-1"></i></div>',
                    className: 'bg-transparent',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                })
            }).addTo(historyLayer).bindPopup("<b>Inicio del Recorrido</b><br>" + new Date(startPos.device_time).toLocaleString());

            L.marker([endPos.latitude, endPos.longitude], {
                icon: L.divIcon({
                    html: '<div class="bg-red-600 text-white rounded-full p-1 w-8 h-8 flex items-center justify-center shadow-lg border-2 border-white"><i class="fas fa-flag-checkered"></i></div>',
                    className: 'bg-transparent',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                })
            }).addTo(historyLayer).bindPopup("<b>Fin del Recorrido</b><br>" + new Date(endPos.device_time).toLocaleString());

            // Mostrar controles
            document.getElementById('close-history-btn').classList.remove('hidden');
            document.getElementById('history-legend').classList.remove('hidden');
        }

        window.clearHistory = function() {
            historyLayer.clearLayers();
            document.getElementById('close-history-btn').classList.add('hidden');
            document.getElementById('history-legend').classList.add('hidden');
        };

        // --- CARGA DE ACTIVOS ---
        function loadAssets() {
            fetch('{{ route("client.api.assets") }}')
                .then(r => r.json())
                .then(data => {
                    renderList(data.assets);
                    renderMap(data.assets);
                })
                .catch(e => console.error(e));
        }

        function renderList(assets) {
            const container = document.getElementById('assets-list');
            container.innerHTML = '';
            if(assets.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-600 text-xs mt-4">Sin activos.</p>'; return;
            }
            assets.forEach(asset => {
                const el = document.createElement('div');
                let icon = asset.type === 'alarm' ? 'fa-shield-alt' : 'fa-car'; 
                let color = (asset.status === 'online' || asset.status === 'armed') ? 'text-green-500' : 'text-gray-500';
                if(asset.status === 'alarm') color = 'text-red-500 animate-pulse';

                el.className = "bg-gray-900 p-3 rounded border border-gray-800 hover:border-gray-600 cursor-pointer transition flex items-center justify-between group hover:bg-gray-800";
                el.innerHTML = `
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="${color} w-8 text-center text-lg"><i class="fas ${icon}"></i></div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-gray-300 group-hover:text-white truncate">${asset.name}</h4>
                            <p class="text-[10px] text-gray-500 truncate capitalize">${asset.type === 'gps' ? (asset.speed + ' km/h') : asset.status}</p>
                        </div>
                    </div>`;
                el.onclick = () => { if(asset.lat) map.flyTo([asset.lat, asset.lng], 16); };
                container.appendChild(el);
            });
        }

        function renderMap(assets) {
            assets.forEach(asset => {
                if(!asset.lat) return;
                let key = `${asset.type}_${asset.id}`;
                let html = asset.type === 'alarm' 
                    ? `<div style="font-size:30px; color:${asset.status === 'armed'?'#10B981':'#6B7280'}; text-align:center; filter:drop-shadow(0 2px 2px rgba(0,0,0,0.5))"><i class="fas fa-shield-alt"></i></div>`
                    : `<div style="font-size:26px; color:${asset.speed > 0?'#3B82F6':'#9CA3AF'}; transform:rotate(${asset.course}deg); text-align:center; filter:drop-shadow(0 2px 2px rgba(0,0,0,0.5))"><i class="fas fa-location-arrow"></i></div>`;

                let icon = L.divIcon({ className: 'bg-transparent', html: html, iconSize: [40,40], iconAnchor: [20,20] });

                if(markers[key]) markers[key].setLatLng([asset.lat, asset.lng]).setIcon(icon);
                else {
                    let m = L.marker([asset.lat, asset.lng], {icon: icon}).addTo(map);
                    m.on('click', () => openModal(asset.type, asset.id));
                    markers[key] = m;
                }
            });
        }

        // --- GESTIÓN DE MODALES ---
        function openModal(type, id) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            overlay.classList.remove('hidden');
            setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); content.classList.add('scale-100', 'opacity-100'); }, 10);
            
            content.innerHTML = '<div class="p-12 text-center"><i class="fas fa-circle-notch fa-spin text-3xl text-blue-500"></i></div>';

            fetch(`/modal/${type}/${id}`)
                .then(r => r.text())
                .then(html => {
                    content.innerHTML = html;
                    if(document.querySelector('.datepicker')) {
                        flatpickr(".datepicker", { enableTime: true, dateFormat: "Y-m-d H:i", locale: "es", theme: "dark" });
                    }
                });
        }

        window.closeModal = function() {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            content.classList.remove('scale-100', 'opacity-100'); content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 200);
        };

        // Alertas
        function toggleAlerts() { document.getElementById('alerts-panel').classList.toggle('translate-x-full'); }
        function loadAlerts() {
            fetch('{{ route("client.api.alerts") }}').then(r=>r.json()).then(d=>{
                const c=document.getElementById('alerts-content'); c.innerHTML='';
                if(d.length){ 
                    document.getElementById('alert-badge').classList.remove('hidden');
                    d.forEach(a=>c.innerHTML+=`<div class="bg-gray-800 p-3 rounded border-l-2 border-red-500 text-sm"><strong class="text-red-400">${a.device}</strong><br><span class="text-gray-300">${a.message}</span><div class="text-xs text-right text-gray-500">${a.time}</div></div>`);
                }
            });
        }

        // Init
        setInterval(loadAssets, 10000); loadAssets(); loadAlerts();
    </script>
</body>
</html>