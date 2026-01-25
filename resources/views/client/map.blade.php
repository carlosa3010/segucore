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
        
        /* Sidebar layout */
        .sidebar-container { display: flex; flex-direction: column; height: 100vh; background: #000; border-right: 1px solid #222; }
        .sidebar-content { flex: 1; overflow-y: auto; }
        .sidebar-footer { flex-shrink: 0; background: #0a0a0a; border-top: 1px solid #222; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }

        /* Iconos Mapa */
        .marker-pin {
            width: 30px; height: 30px; border-radius: 50% 50% 50% 0;
            position: absolute; transform: rotate(-45deg);
            left: 50%; top: 50%; margin: -15px 0 0 -15px;
            box-shadow: 0 3px 5px rgba(0,0,0,0.3);
        }
        .marker-pin::after {
            content: ''; width: 20px; height: 20px; margin: 5px 0 0 5px;
            background: #fff; position: absolute; border-radius: 50%;
        }
    </style>
</head>
<body class="overflow-hidden flex bg-black text-gray-200">

    <div class="w-80 sidebar-container z-20 relative transition-transform duration-300" id="sidebar">
        
        <div class="p-5 border-b border-gray-800 bg-black">
            <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-shield-alt text-2xl text-white"></i>
                <span class="font-bold tracking-widest text-lg text-white">SEGUCORE</span>
            </div>
            <input type="text" id="searchInput" placeholder="Buscar..." 
                   class="w-full bg-gray-900 border border-gray-700 text-sm rounded px-3 py-2 text-white focus:outline-none focus:border-gray-500">
        </div>

        <div class="grid grid-cols-2 border-b border-gray-800">
            <button class="py-3 text-xs font-bold text-gray-400 hover:text-white hover:bg-gray-900 transition border-r border-gray-800">
                <i class="fas fa-bell mr-1"></i> ALERTAS
            </button>
            <button onclick="loadModal('{{ route('client.modal.billing') }}')" class="py-3 text-xs font-bold text-gray-400 hover:text-white hover:bg-gray-900 transition">
                <i class="fas fa-file-invoice mr-1"></i> FACTURAS
            </button>
        </div>

        <div class="sidebar-content p-2 space-y-2" id="assets-list">
            <div class="text-center mt-10 text-gray-600 text-xs">Cargando activos...</div>
        </div>

        <div class="sidebar-footer p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded bg-gray-800 flex items-center justify-center font-bold text-white text-xs border border-gray-700">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="font-bold text-white text-sm truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-green-500">● Conectado</p>
                </div>
            </div>
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button type="submit" class="w-full bg-gray-900 hover:bg-red-900/30 text-gray-400 hover:text-red-400 text-xs font-bold py-2 rounded border border-gray-800 hover:border-red-900 transition">
                    <i class="fas fa-power-off mr-2"></i> CERRAR SESIÓN
                </button>
            </form>
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
        // 1. Mapa Minimalista
        const map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '© SeguCore', maxZoom: 19
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
                });
        }
        setInterval(loadAssets, 15000); // Polling cada 15s
        loadAssets();

        // 3. Render Lista Lateral
        function renderList(assets) {
            const container = document.getElementById('assets-list');
            container.innerHTML = '';

            if(assets.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-600 text-xs mt-4">Sin activos asignados</p>';
                return;
            }

            assets.forEach(asset => {
                const el = document.createElement('div');
                // Icono según tipo
                let iconClass = asset.type === 'alarm' ? 'fa-home' : 'fa-car';
                let statusColor = (asset.status === 'online' || asset.status === 'armed') ? 'text-green-500' : 'text-gray-500';

                el.className = "bg-gray-900 p-3 rounded border border-gray-800 hover:border-gray-600 cursor-pointer transition flex items-center justify-between group";
                el.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="${statusColor} w-6 text-center"><i class="fas ${iconClass}"></i></div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-300 group-hover:text-white">${asset.name}</h4>
                            <p class="text-[10px] text-gray-500">${asset.type === 'gps' ? asset.speed + ' km/h' : asset.status}</p>
                        </div>
                    </div>
                    <i class="fas fa-location-arrow text-gray-700 group-hover:text-white text-xs"></i>
                `;
                // CLICK EN LISTA: Solo mueve el mapa
                el.onclick = () => {
                    map.flyTo([asset.lat, asset.lng], 16);
                };
                container.appendChild(el);
            });
        }

        // 4. Render Mapa
        function renderMap(assets) {
            assets.forEach(asset => {
                if(!asset.lat || !asset.lng) return;

                // Definir HTML del icono
                let html = '';
                if (asset.type === 'alarm') {
                    // Icono de Casa / Escudo para alarma
                    let color = asset.status === 'armed' ? '#10B981' : '#EF4444'; 
                    html = `<div style="font-size: 26px; color: ${color}; filter: drop-shadow(2px 2px 2px rgba(0,0,0,0.5)); text-align: center;">
                                <i class="fas fa-shield-alt"></i>
                            </div>`;
                } else {
                    // Icono de Flecha para GPS
                    let color = asset.speed > 0 ? '#3B82F6' : '#6B7280';
                    html = `<div style="font-size: 24px; color: ${color}; transform: rotate(${asset.course}deg); filter: drop-shadow(2px 2px 2px rgba(0,0,0,0.5)); text-align: center;">
                                <i class="fas fa-location-arrow"></i>
                            </div>`;
                }

                let icon = L.divIcon({
                    className: 'bg-transparent',
                    html: html,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });

                if (markers[asset.id]) {
                    markers[asset.id].setLatLng([asset.lat, asset.lng]).setIcon(icon);
                } else {
                    let m = L.marker([asset.lat, asset.lng], { icon: icon }).addTo(map);
                    // CLICK EN MAPA: Abre el modal
                    m.on('click', () => {
                        openModal(asset.type, asset.id);
                    });
                    markers[asset.id] = m;
                }
            });
        }

        // 5. Modal
        function openModal(type, id) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            
            overlay.classList.remove('hidden');
            // Animación entrada
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);

            content.innerHTML = '<div class="p-10 text-center"><i class="fas fa-circle-notch fa-spin text-3xl text-white"></i></div>';

            fetch(`/portal/modal/${type}/${id}`)
                .then(r => r.text())
                .then(html => {
                    content.innerHTML = html;
                    // Re-inicializar datepickers si el HTML los contiene
                    if(document.querySelector('.datepicker')) {
                        flatpickr(".datepicker", { enableTime: true, dateFormat: "Y-m-d H:i", locale: "es", theme: "dark" });
                    }
                })
                .catch(() => {
                    content.innerHTML = '<div class="p-4 text-center text-red-500">Error cargando información.</div>';
                });
        }

        function closeModal() {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 200);
        }

        // Exponer cerrar modal globalmente
        window.closeModal = closeModal;
    </script>
</body>
</html>