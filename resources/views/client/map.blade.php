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
        
        /* Sidebar Negra Total */
        .sidebar { background: #000000; border-right: 1px solid #333; }
        
        /* Scrollbar oscura */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #111; }
        ::-webkit-scrollbar-thumb { background: #444; border-radius: 2px; }

        /* Panel Alertas (Derecha) */
        #alerts-panel {
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(5px);
            border-left: 1px solid #333;
            box-shadow: -5px 0 15px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body class="overflow-hidden flex bg-black text-gray-100">

    <div class="w-80 h-full sidebar z-30 flex flex-col relative transition-transform duration-300" id="sidebar">
        
        <div class="p-5 border-b border-gray-800 bg-neutral-900">
            <div class="flex items-center gap-2 mb-4">
                <img src="{{ asset('images/logo-white.png') }}" alt="Logo" class="h-8">
                <span class="font-bold tracking-widest text-lg">SEGUCORE</span>
            </div>
            
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Buscar activo..." 
                       class="w-full bg-black border border-gray-700 text-gray-300 text-sm rounded px-3 py-2 pl-9 focus:border-white focus:outline-none transition">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-px bg-gray-800 border-b border-gray-800">
            <button onclick="toggleAlerts()" class="bg-neutral-900 hover:bg-black text-gray-400 hover:text-white py-3 text-xs font-bold uppercase transition relative">
                <i class="fas fa-bell mr-1"></i> Alertas
                <span id="alert-dot" class="hidden absolute top-2 right-8 w-2 h-2 bg-red-600 rounded-full"></span>
            </button>
            <button onclick="loadModal('{{ route('client.modal.billing') }}')" class="bg-neutral-900 hover:bg-black text-gray-400 hover:text-white py-3 text-xs font-bold uppercase transition">
                <i class="fas fa-file-invoice mr-1"></i> Facturas
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-2" id="assets-list">
            </div>

        <div class="p-4 bg-neutral-900 border-t border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded bg-gray-700 flex items-center justify-center font-bold text-white text-xs">
                        {{ substr(Auth::user()->name, 0, 2) }}
                    </div>
                    <div class="leading-tight">
                        <p class="text-sm font-bold text-white">{{ Str::limit(Auth::user()->name, 15) }}</p>
                        <p class="text-[10px] text-green-500">Online</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-white transition"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </div>
    </div>

    <div id="alerts-panel" class="fixed right-0 top-0 bottom-0 w-80 z-40 transform translate-x-full transition-transform duration-300">
        <div class="flex items-center justify-between p-4 border-b border-gray-800 bg-neutral-900">
            <h3 class="font-bold text-sm text-gray-200 uppercase">Notificaciones</h3>
            <button onclick="toggleAlerts()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div id="alerts-content" class="p-4 space-y-3 overflow-y-auto h-full pb-20">
            </div>
    </div>

    <div class="flex-1 relative bg-gray-900">
        <div id="map"></div>

        <div id="history-player" class="hidden absolute bottom-8 left-1/2 transform -translate-x-1/2 bg-black text-white p-3 rounded-lg shadow-xl border border-gray-700 z-[500] flex items-center gap-4 w-96">
            <button onclick="togglePlay()" id="play-btn" class="text-white hover:text-green-400"><i class="fas fa-play"></i></button>
            <input type="range" id="seek-bar" class="flex-1 h-1 bg-gray-700 rounded-lg appearance-none cursor-pointer">
            <button onclick="closeHistory()" class="text-gray-500 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div id="modal-overlay" class="fixed inset-0 bg-black/80 z-[60] hidden flex items-center justify-center backdrop-blur-sm">
        <div id="modal-box" class="bg-neutral-900 border border-gray-700 w-full max-w-2xl rounded-lg shadow-2xl transform scale-95 opacity-0 transition-all duration-200">
            <div class="flex justify-between items-center p-4 border-b border-gray-800">
                <h3 class="font-bold text-white text-lg" id="modal-title">Detalles</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div id="modal-body" class="p-0">
                </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        // 1. MAPA (Oscuro/Neutro)
        const map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; SeguCore',
            maxZoom: 19
        }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        let markers = {};
        let assetsCache = [];

        // 2. CARGA DE DATOS
        function refreshData() {
            fetch('{{ route("client.api.assets") }}')
                .then(r => r.json())
                .then(d => {
                    assetsCache = d.assets;
                    renderList(assetsCache);
                    renderMarkers(assetsCache);
                });
        }
        setInterval(refreshData, 10000);
        refreshData();
        loadAlerts();

        // 3. RENDER LISTA LATERAL
        function renderList(assets) {
            const container = document.getElementById('assets-list');
            container.innerHTML = '';
            
            assets.forEach(asset => {
                let icon = asset.type === 'alarm' ? 'fa-shield-alt' : 'fa-car';
                let color = (asset.status === 'online' || asset.status === 'armed') ? 'text-green-500' : 'text-gray-500';
                
                let div = document.createElement('div');
                div.className = "p-3 rounded bg-gray-900/50 hover:bg-gray-800 border border-transparent hover:border-gray-700 cursor-pointer transition group";
                div.onclick = () => flyTo(asset);
                div.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas ${icon} ${color} text-lg w-6 text-center"></i>
                            <div>
                                <h4 class="font-bold text-gray-200 text-sm group-hover:text-white">${asset.name}</h4>
                                <p class="text-[10px] text-gray-500">${asset.type === 'gps' ? asset.speed + ' km/h' : asset.status}</p>
                            </div>
                        </div>
                    </div>`;
                container.appendChild(div);
            });
        }

        // 4. RENDER MARCADORES (SOLUCION ALARMAS)
        function renderMarkers(assets) {
            assets.forEach(asset => {
                let lat = parseFloat(asset.lat);
                let lng = parseFloat(asset.lng);

                if (isNaN(lat) || isNaN(lng) || lat === 0) return; // Saltar inválidos

                // Iconos personalizados
                let html = '';
                if(asset.type === 'alarm') {
                     let color = asset.status === 'armed' ? '#10B981' : '#EF4444'; // Verde o Rojo
                     html = `<div style="color:${color}; font-size:24px; filter:drop-shadow(0 2px 2px rgba(0,0,0,0.5));"><i class="fas fa-home"></i></div>`;
                } else {
                     // GPS con rotación
                     let color = asset.speed > 0 ? '#10B981' : '#6B7280';
                     html = `<div style="transform: rotate(${asset.course}deg); color:${color}; font-size:24px; filter:drop-shadow(0 2px 2px rgba(0,0,0,0.5));"><i class="fas fa-location-arrow"></i></div>`;
                }

                let icon = L.divIcon({ className: 'bg-transparent', html: html, iconSize: [30,30], iconAnchor: [15,15] });

                if (markers[asset.id]) {
                    markers[asset.id].setLatLng([lat, lng]).setIcon(icon);
                } else {
                    let m = L.marker([lat, lng], {icon: icon}).addTo(map);
                    m.on('click', () => openModal(asset.type, asset.id));
                    m.bindTooltip(asset.name, { direction: 'top', offset: [0, -10], className: 'bg-black text-white border-0' });
                    markers[asset.id] = m;
                }
            });
        }

        function flyTo(asset) {
            map.setView([asset.lat, asset.lng], 16);
            openModal(asset.type, asset.id);
        }

        // 5. ALERTAS (Panel Derecho)
        function toggleAlerts() {
            const panel = document.getElementById('alerts-panel');
            if (panel.classList.contains('translate-x-full')) {
                panel.classList.remove('translate-x-full');
            } else {
                panel.classList.add('translate-x-full');
            }
        }

        function loadAlerts() {
            fetch('{{ route("client.api.alerts") }}')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('alerts-content');
                    if(data.length > 0) {
                        document.getElementById('alert-dot').classList.remove('hidden');
                        list.innerHTML = data.map(a => `
                            <div class="bg-gray-800 p-3 rounded border-l-2 border-red-500">
                                <p class="text-red-400 font-bold text-xs mb-1">${a.device}</p>
                                <p class="text-gray-300 text-sm">${a.message}</p>
                                <p class="text-gray-600 text-[10px] text-right mt-1">${a.time}</p>
                            </div>
                        `).join('');
                    } else {
                        list.innerHTML = '<p class="text-gray-500 text-center mt-10 text-sm">Sin novedades.</p>';
                    }
                });
        }

        // 6. MODAL (Sin subventanas)
        function openModal(type, id) {
            const overlay = document.getElementById('modal-overlay');
            const box = document.getElementById('modal-box');
            const body = document.getElementById('modal-body');

            overlay.classList.remove('hidden');
            setTimeout(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }, 10);

            body.innerHTML = '<div class="p-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-white"></i></div>';

            fetch(`/portal/modal/${type}/${id}`)
                .then(r => r.text())
                .then(html => {
                    body.innerHTML = html;
                    if(document.querySelector('.datepicker')) {
                        flatpickr(".datepicker", { 
                            enableTime: true, dateFormat: "Y-m-d H:i", 
                            locale: "es", theme: "dark" 
                        });
                    }
                })
                .catch(e => {
                    body.innerHTML = '<p class="p-4 text-red-500 text-center">Error cargando datos.</p>';
                });
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            const box = document.getElementById('modal-box');
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 200);
        }
        
        // Exponer funciones globales para el historial
        window.loadHistoryTrace = function(data) {
           // Aquí iría la lógica de dibujar polilínea (implementada en paso anterior, simplificada aquí)
           closeModal();
           alert("Historial cargado: " + data.length + " puntos. (Implementar dibujo aqui)");
        }
    </script>
</body>
</html>