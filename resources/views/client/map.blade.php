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
        body { font-family: 'Inter', sans-serif; background: #000; color: #e5e7eb; }
        #map { height: 100vh; width: 100%; z-index: 1; }
        
        /* Sidebar */
        .sidebar-container { display: flex; flex-direction: column; height: 100vh; background: #000; border-right: 1px solid #222; }
        .sidebar-content { flex: 1; overflow-y: auto; }
        
        /* Scrollbar Fina */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }

        /* Panel Alertas */
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
                <input type="text" id="searchInput" placeholder="Buscar activo..." 
                       class="w-full bg-gray-900 border border-gray-700 text-sm rounded px-3 py-2 pl-9 text-gray-200 focus:outline-none focus:border-gray-500 transition">
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
        <div id="alerts-content" class="flex-1 overflow-y-auto p-4 space-y-3">
            </div>
    </div>

    <div class="flex-1 relative bg-gray-900">
        <div id="map"></div>
    </div>

    <div id="modal-overlay" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
        <div id="modal-content" class="bg-gray-900 border border-gray-700 w-full max-w-2xl rounded-lg shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-200 min-h-[200px]">
            </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        // 1. Mapa en Modo Claro (Clean)
        const map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '', maxZoom: 19
        }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        let markers = {}; 

        // 2. Cargar Datos
        function loadAssets() {
            fetch('{{ route("client.api.assets") }}')
                .then(r => r.json())
                .then(data => {
                    renderList(data.assets);
                    renderMap(data.assets);
                })
                .catch(e => console.error("Error assets:", e));
        }

        // 3. Render Lista Lateral (MENU IZQUIERDO)
        function renderList(assets) {
            const container = document.getElementById('assets-list');
            container.innerHTML = '';

            if(assets.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-600 text-xs mt-4">Sin activos.</p>';
                return;
            }

            assets.forEach(asset => {
                const el = document.createElement('div');
                
                // ICONO CORREGIDO PARA ALARMA
                let iconClass = asset.type === 'alarm' ? 'fa-shield-alt' : 'fa-car'; 
                let statusColor = (asset.status === 'online' || asset.status === 'armed') ? 'text-green-500' : 'text-gray-500';
                if(asset.status === 'alarm') statusColor = 'text-red-500 animate-pulse';

                el.className = "bg-gray-900 p-3 rounded border border-gray-800 hover:border-gray-600 cursor-pointer transition flex items-center justify-between group hover:bg-gray-800";
                
                el.innerHTML = `
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="${statusColor} w-8 text-center text-lg"><i class="fas ${iconClass}"></i></div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-gray-300 group-hover:text-white truncate">${asset.name}</h4>
                            <p class="text-[10px] text-gray-500 truncate capitalize">
                                ${asset.type === 'gps' ? (asset.speed + ' km/h') : (asset.status === 'armed' ? 'Armado' : 'Desarmado')}
                            </p>
                        </div>
                    </div>
                `;
                
                el.onclick = () => {
                    if(asset.lat && asset.lng) {
                        map.flyTo([asset.lat, asset.lng], 16);
                        // openModal(asset.type, asset.id); // Descomentar si quieres abrir modal al clickear en lista
                    }
                };
                container.appendChild(el);
            });
        }

        // 4. Render Mapa
        function renderMap(assets) {
            assets.forEach(asset => {
                if(!asset.lat || !asset.lng) return;

                let uniqueKey = `${asset.type}_${asset.id}`;
                let html = '';

                // ICONOS EN EL MAPA
                if (asset.type === 'alarm') {
                    let color = asset.status === 'armed' ? '#10B981' : (asset.status === 'alarm' ? '#EF4444' : '#6B7280'); 
                    html = `<div style="font-size: 30px; color: ${color}; filter: drop-shadow(0 3px 2px rgba(0,0,0,0.4)); text-align: center;">
                                <i class="fas fa-shield-alt"></i>
                            </div>`;
                } else {
                    let color = asset.speed > 0 ? '#3B82F6' : '#9CA3AF';
                    html = `<div style="font-size: 26px; color: ${color}; transform: rotate(${asset.course}deg); filter: drop-shadow(0 3px 2px rgba(0,0,0,0.4)); text-align: center;">
                                <i class="fas fa-location-arrow"></i>
                            </div>`;
                }

                let icon = L.divIcon({ className: 'bg-transparent', html: html, iconSize: [40, 40], iconAnchor: [20, 20] });

                if (markers[uniqueKey]) {
                    markers[uniqueKey].setLatLng([asset.lat, asset.lng]).setIcon(icon);
                } else {
                    let m = L.marker([asset.lat, asset.lng], { icon: icon }).addTo(map);
                    m.on('click', () => openModal(asset.type, asset.id));
                    markers[uniqueKey] = m;
                }
            });
        }

        // 5. MODALES (CORREGIDO ERROR 404 y SUBVENTANA)
        function openModal(type, id) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            
            overlay.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);

            content.innerHTML = '<div class="p-12 text-center"><i class="fas fa-circle-notch fa-spin text-3xl text-blue-500"></i></div>';

            // CORRECCIÓN CLAVE: URL CORRECTA SIN "/portal"
            fetch(`/modal/${type}/${id}`)
                .then(r => {
                    if (!r.ok) throw new Error("Error HTTP " + r.status);
                    return r.text();
                })
                .then(html => {
                    content.innerHTML = html;
                    if(document.querySelector('.datepicker')) {
                        flatpickr(".datepicker", { enableTime: true, dateFormat: "Y-m-d H:i", locale: "es", theme: "dark" });
                    }
                })
                .catch(err => {
                    console.error(err);
                    content.innerHTML = '<div class="p-6 text-center text-red-500">Error al cargar datos.<br><span class="text-xs text-gray-500">Verifica la conexión.</span></div>';
                    setTimeout(closeModal, 2000);
                });
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 200);
        }

        // Alertas
        function toggleAlerts() {
            document.getElementById('alerts-panel').classList.toggle('translate-x-full');
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
                                <div class="bg-gray-800 p-3 rounded border-l-2 border-red-500 text-sm">
                                    <strong class="text-red-400">${a.device}</strong><br>
                                    <span class="text-gray-300">${a.message}</span>
                                    <div class="text-xs text-gray-600 text-right mt-1">${a.time}</div>
                                </div>`;
                        });
                    } else {
                        badge.classList.add('hidden');
                        list.innerHTML = '<p class="text-center text-gray-600 text-xs mt-4">Sin novedades.</p>';
                    }
                });
        }

        // Iniciar
        setInterval(loadAssets, 10000);
        loadAssets();
        loadAlerts();
        window.closeModal = closeModal; // Exponer globalmente
    </script>
</body>
</html>