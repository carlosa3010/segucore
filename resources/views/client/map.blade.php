<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeguCore Cliente</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background: #000; }
        #map { height: 100vh; width: 100%; z-index: 1; }
        
        /* Layout Sidebar */
        .sidebar-container { display: flex; flex-direction: column; height: 100vh; background: #000; border-right: 1px solid #222; }
        .sidebar-content { flex: 1; overflow-y: auto; }
        .sidebar-footer { flex-shrink: 0; background: #0a0a0a; border-top: 1px solid #222; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }

        /* Panel Alertas */
        #alerts-panel {
            box-shadow: -5px 0 15px rgba(0,0,0,0.8);
            border-left: 1px solid #222;
        }
    </style>
</head>
<body class="overflow-hidden flex bg-black text-gray-200">

    <div class="w-80 sidebar-container z-20 relative transition-transform duration-300" id="sidebar">
        
        <div class="p-5 border-b border-gray-800 bg-black">
            <div class="flex items-center justify-center mb-6">
                <img src="{{ asset('images/logo-white.png') }}" alt="SeguCore" class="h-12 object-contain">
            </div>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Buscar activo..." 
                       class="w-full bg-gray-900 border border-gray-700 text-sm rounded px-3 py-2 pl-9 text-white focus:outline-none focus:border-gray-500 transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-2 border-b border-gray-800 bg-gray-900/50">
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

        <div class="sidebar-footer p-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded bg-gray-800 flex items-center justify-center font-bold text-white text-sm border border-gray-700 shadow-lg">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="font-bold text-white text-sm truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Conectado</p>
                </div>
            </div>
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button type="submit" class="w-full bg-gray-900 hover:bg-red-900/20 text-gray-400 hover:text-red-400 text-xs font-bold py-3 rounded border border-gray-800 hover:border-red-900/50 transition flex items-center justify-center gap-2">
                    <i class="fas fa-power-off"></i> CERRAR SESIÓN
                </button>
            </form>
        </div>
    </div>

    <div id="alerts-panel" class="fixed right-0 top-0 bottom-0 w-80 z-30 bg-black/95 backdrop-blur transform translate-x-full transition-transform duration-300 flex flex-col">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-900">
            <h3 class="font-bold text-sm text-gray-200 uppercase tracking-wide">Notificaciones</h3>
            <button onclick="toggleAlerts()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div id="alerts-content" class="flex-1 overflow-y-auto p-4 space-y-3">
            </div>
    </div>

    <div class="flex-1 relative bg-gray-900">
        <div id="map"></div>
    </div>

    <div id="modal-overlay" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
        <div id="modal-content" class="bg-gray-900 border border-gray-700 w-full max-w-2xl rounded-lg shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        // 1. CONFIGURACIÓN DEL MAPA
        const map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© SeguCore', maxZoom: 19
        }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Usamos un objeto con claves compuestas para evitar colisiones
        let markers = {}; 

        // 2. CICLO DE DATOS
        function loadAssets() {
            fetch('{{ route("client.api.assets") }}')
                .then(r => r.json())
                .then(data => {
                    renderList(data.assets);
                    renderMap(data.assets);
                })
                .catch(err => console.error("Error cargando activos:", err));
        }
        
        function loadAlerts() {
            fetch('{{ route("client.api.alerts") }}')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('alerts-content');
                    const badge = document.getElementById('alert-badge');
                    
                    list.innerHTML = '';
                    if(data.length > 0) {
                        badge.classList.remove('hidden');
                        data.forEach(a => {
                            list.innerHTML += `
                                <div class="bg-gray-800 p-3 rounded border-l-2 border-red-500">
                                    <div class="flex justify-between items-start">
                                        <span class="text-red-400 font-bold text-xs">${a.device}</span>
                                        <span class="text-gray-600 text-[10px]">${a.time}</span>
                                    </div>
                                    <p class="text-gray-300 text-sm mt-1 leading-snug">${a.message}</p>
                                </div>`;
                        });
                    } else {
                        badge.classList.add('hidden');
                        list.innerHTML = '<p class="text-gray-600 text-center text-sm mt-10">Sin alertas recientes.</p>';
                    }
                });
        }

        setInterval(loadAssets, 10000); // 10s activos
        setInterval(loadAlerts, 30000); // 30s alertas
        loadAssets();
        loadAlerts();

        // 3. RENDER LISTA
        function renderList(assets) {
            const container = document.getElementById('assets-list');
            container.innerHTML = '';

            if(assets.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-600 text-xs mt-4">No hay activos para mostrar.</p>';
                return;
            }

            assets.forEach(asset => {
                const el = document.createElement('div');
                let iconClass = asset.type === 'alarm' ? 'fa-house-shield' : 'fa-car'; // Icono diferente para alarma
                let statusColor = (asset.status === 'online' || asset.status === 'armed') ? 'text-green-500' : 'text-gray-500';
                if(asset.status === 'alarm') statusColor = 'text-red-500 animate-pulse';

                el.className = "bg-gray-900 p-3 rounded border border-gray-800 hover:border-gray-600 cursor-pointer transition flex items-center justify-between group hover:bg-gray-800";
                
                el.innerHTML = `
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="${statusColor} w-8 text-center text-lg"><i class="fas ${iconClass}"></i></div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-gray-300 group-hover:text-white truncate">${asset.name}</h4>
                            <p class="text-[10px] text-gray-500 truncate capitalize">${asset.type === 'gps' ? (asset.speed + ' km/h') : asset.status}</p>
                        </div>
                    </div>
                    <button class="text-gray-600 group-hover:text-blue-400 hover:bg-blue-900/20 p-2 rounded-full transition">
                        <i class="fas fa-crosshairs"></i>
                    </button>
                `;
                
                // CLICK: Ubicar en mapa
                el.onclick = () => {
                    if(asset.lat && asset.lng) {
                        map.flyTo([asset.lat, asset.lng], 16);
                        // Opcional: Abrir modal automáticamente
                        // openModal(asset.type, asset.id); 
                    } else {
                        alert("Este activo no tiene coordenadas reportadas.");
                    }
                };
                container.appendChild(el);
            });
        }

        // 4. RENDER MAPA (Marcadores)
        function renderMap(assets) {
            assets.forEach(asset => {
                if(!asset.lat || !asset.lng || asset.lat == 0) return;

                // CLAVE ÚNICA para evitar conflicto de IDs entre Alarmas y GPS
                let uniqueKey = `${asset.type}_${asset.id}`;

                // HTML del Icono
                let html = '';
                if (asset.type === 'alarm') {
                    let color = asset.status === 'armed' ? '#10B981' : (asset.status === 'alarm' ? '#EF4444' : '#6B7280'); 
                    // Icono de Escudo Grande
                    html = `<div style="font-size: 32px; color: ${color}; filter: drop-shadow(0 4px 3px rgba(0,0,0,0.5)); text-align: center; margin-top: -10px;">
                                <i class="fas fa-shield-halved"></i>
                            </div>`;
                } else {
                    let color = asset.speed > 0 ? '#3B82F6' : '#9CA3AF';
                    // Flecha de GPS
                    html = `<div style="font-size: 26px; color: ${color}; transform: rotate(${asset.course}deg); filter: drop-shadow(0 3px 3px rgba(0,0,0,0.5)); text-align: center;">
                                <i class="fas fa-location-arrow"></i>
                            </div>`;
                }

                let icon = L.divIcon({
                    className: 'bg-transparent',
                    html: html,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20] // Centro
                });

                if (markers[uniqueKey]) {
                    // Actualizar posición e icono
                    markers[uniqueKey].setLatLng([asset.lat, asset.lng]).setIcon(icon);
                } else {
                    // Crear nuevo
                    let m = L.marker([asset.lat, asset.lng], { icon: icon }).addTo(map);
                    m.bindTooltip(asset.name, { direction: 'top', offset: [0, -20], className: 'bg-black text-white border-none px-2 py-1 rounded text-xs' });
                    
                    // CLICK EN MARCADOR: Abre Modal
                    m.on('click', () => {
                        openModal(asset.type, asset.id);
                    });
                    
                    markers[uniqueKey] = m;
                }
            });
        }

        // 5. MODALES Y UI
        function toggleAlerts() {
            document.getElementById('alerts-panel').classList.toggle('translate-x-full');
        }

        function openModal(type, id) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            
            overlay.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);

            content.innerHTML = '<div class="p-12 text-center"><i class="fas fa-circle-notch fa-spin text-4xl text-blue-500"></i><p class="text-gray-500 mt-4 text-sm">Obteniendo detalles...</p></div>';

            fetch(`/portal/modal/${type}/${id}`)
                .then(r => r.text())
                .then(html => {
                    content.innerHTML = html;
                    if(document.querySelector('.datepicker')) {
                        flatpickr(".datepicker", { enableTime: true, dateFormat: "Y-m-d H:i", locale: "es", theme: "dark" });
                    }
                })
                .catch(() => {
                    content.innerHTML = '<div class="p-6 text-center text-red-500"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><br>Error de conexión.</div>';
                });
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 200);
        }

        window.closeModal = closeModal; // Global scope para onclicks
    </script>
</body>
</html>