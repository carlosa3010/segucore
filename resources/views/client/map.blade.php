<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Segusmart Cliente</title>
    
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
        
        <div id="map-controls" class="hidden absolute top-4 right-4 z-[400] flex flex-col gap-2 items-end">
            
            <a id="btn-pdf-map" href="#" target="_blank" class="bg-red-600 text-white font-bold px-4 py-2 rounded shadow-lg hover:bg-red-500 text-xs flex items-center gap-2 transition transform hover:scale-105">
                <i class="fas fa-file-pdf text-base"></i> DESCARGAR REPORTE
            </a>

            <button onclick="clearHistory()" class="bg-white text-black font-bold px-4 py-2 rounded shadow-lg hover:bg-gray-200 text-xs flex items-center gap-2 transition">
                <i class="fas fa-times text-red-500"></i> LIMPIAR RUTA
            </button>
        </div>

        <div id="history-legend" class="hidden absolute bottom-8 right-4 z-[400] bg-gray-900/90 p-3 rounded-lg border border-gray-700 text-xs text-gray-300 shadow-2xl backdrop-blur w-40">
            <h5 class="font-bold text-white mb-2 border-b border-gray-700 pb-1 text-center uppercase tracking-wider">Leyenda</h5>
            <div class="space-y-1.5">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-black border border-gray-500"></span> <span>Detenido</span></div>
                <div class="flex items-center gap-2"><span class="w-8 h-1.5 rounded-full bg-green-500"></span> <span>1-40 km/h</span></div>
                <div class="flex items-center gap-2"><span class="w-8 h-1.5 rounded-full bg-yellow-500"></span> <span>40-80 km/h</span></div>
                <div class="flex items-center gap-2"><span class="w-8 h-1.5 rounded-full bg-red-600"></span> <span>+80 km/h</span></div>
            </div>
        </div>
    </div>

    <div id="modal-overlay" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
        <div id="modal-content" class="bg-gray-900 border border-gray-700 w-full max-w-2xl rounded-lg shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-200 min-h-[200px]"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        const map = L.map('map', { zoomControl: false }).setView([10.4806, -66.9036], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { attribution: '', maxZoom: 19 }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        let markers = {}; 
        let historyLayer = L.layerGroup().addTo(map); 

        // --- FUNCIONES GLOBALES ---

        window.sendCommand = function(deviceId, type) {
            if(!confirm('¿ATENCIÓN: Está seguro de enviar este comando?')) return;
            const feedback = document.getElementById('command-feedback');
            feedback.innerHTML = '<span class="text-blue-400 animate-pulse">Enviando...</span>';

            fetch(`/api/device/${deviceId}/command`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ type: type })
            })
            .then(r => r.json())
            .then(d => feedback.innerHTML = d.success ? '<span class="text-green-500 font-bold">Éxito</span>' : '<span class="text-red-500">Error</span>')
            .catch(() => feedback.innerHTML = '<span class="text-red-500">Error Red</span>');
        };

        window.fetchHistory = function(deviceId) {
            const btn = document.getElementById('btn-history');
            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> CARGANDO...';

            fetch(`/api/history/${deviceId}?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`)
                .then(r => r.json())
                .then(data => {
                    if(data.error) { alert(data.error); }
                    else if(!data.positions || data.positions.length === 0) { alert("Sin datos."); }
                    else {
                        closeModal();
                        drawHistoryAdvanced(data.positions);
                        // ACTIVAR BOTÓN PDF EN EL MAPA
                        updatePdfButton(deviceId, start, end);
                    }
                })
                .catch(e => alert("Error al cargar historial."))
                .finally(() => { if(btn) { btn.disabled = false; btn.innerHTML = 'CONSULTAR RUTA'; }});
        };

        function updatePdfButton(deviceId, start, end) {
            const btn = document.getElementById('btn-pdf-map');
            btn.href = `/api/history/${deviceId}/pdf?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
        }

        function drawHistoryAdvanced(positions) {
            clearHistory();
            if(positions.length < 2) return;

            const allPoints = positions.map(p => [p.latitude, p.longitude]);
            map.fitBounds(L.polyline(allPoints).getBounds(), {padding: [50, 50]});

            for (let i = 0; i < positions.length - 1; i++) {
                let p1 = positions[i];
                let p2 = positions[i+1];
                let color = p1.speed === 0 ? '#000' : (p1.speed < 40 ? '#10B981' : (p1.speed < 80 ? '#EAB308' : '#EF4444'));

                L.polyline([[p1.latitude, p1.longitude], [p2.latitude, p2.longitude]], { color: color, weight: 5, opacity: 0.8 }).addTo(historyLayer)
                 .bindTooltip(`Vel: ${p1.speed} km/h<br>${new Date(p1.device_time).toLocaleTimeString()}`, { sticky: true });
                
                if (p1.speed === 0) L.circleMarker([p1.latitude, p1.longitude], { radius: 2, color: '#000' }).addTo(historyLayer);
            }

            // Marcadores Inicio/Fin
            L.marker([positions[0].latitude, positions[0].longitude], { icon: createIcon('green', 'play') }).addTo(historyLayer);
            L.marker([positions[positions.length-1].latitude, positions[positions.length-1].longitude], { icon: createIcon('red', 'flag-checkered') }).addTo(historyLayer);

            document.getElementById('map-controls').classList.remove('hidden');
            document.getElementById('history-legend').classList.remove('hidden');
        }

        function createIcon(color, icon) {
            return L.divIcon({
                html: `<div class="bg-${color}-600 text-white rounded-full p-1 w-8 h-8 flex items-center justify-center shadow border-2 border-white"><i class="fas fa-${icon}"></i></div>`,
                className: 'bg-transparent', iconSize: [32, 32], iconAnchor: [16, 32]
            });
        }

        window.clearHistory = function() {
            historyLayer.clearLayers();
            document.getElementById('map-controls').classList.add('hidden');
            document.getElementById('history-legend').classList.add('hidden');
        };

        // --- CARGA DE ACTIVOS ---
        function loadAssets() {
            fetch('{{ route("client.api.assets") }}').then(r => r.json()).then(d => {
                renderList(d.assets); renderMap(d.assets);
            });
        }

        function renderList(assets) {
            const c = document.getElementById('assets-list'); c.innerHTML = '';
            assets.forEach(a => {
                let color = (a.status === 'online' || a.status === 'armed') ? 'text-green-500' : 'text-gray-500';
                if(a.status === 'alarm') color = 'text-red-500 animate-pulse';
                const div = document.createElement('div');
                div.className = "bg-gray-900 p-3 rounded border border-gray-800 hover:border-gray-600 cursor-pointer transition flex justify-between group";
                div.innerHTML = `<div class="flex gap-3"><div class="${color}"><i class="fas ${a.type==='alarm'?'fa-shield-alt':'fa-car'}"></i></div><div><h4 class="text-sm font-bold text-gray-300 group-hover:text-white">${a.name}</h4><p class="text-[10px] text-gray-500">${a.last_update}</p></div></div>`;
                div.onclick = () => { if(a.lat) map.flyTo([a.lat, a.lng], 16); };
                c.appendChild(div);
            });
        }

        function renderMap(assets) {
            assets.forEach(a => {
                if(!a.lat) return;
                let key = `${a.type}_${a.id}`;
                // Iconos simples para evitar problemas de render
                let html = a.type === 'alarm' 
                    ? `<div style="font-size:30px; color:${a.status==='armed'?'#10B981':'#6B7280'}"><i class="fas fa-shield-alt"></i></div>`
                    : `<div style="font-size:24px; color:${a.speed>0?'#3B82F6':'#9CA3AF'}; transform:rotate(${a.course}deg)"><i class="fas fa-location-arrow"></i></div>`;
                
                let icon = L.divIcon({ className: 'bg-transparent', html: html, iconSize: [30,30], iconAnchor: [15,15] });

                if(markers[key]) markers[key].setLatLng([a.lat, a.lng]).setIcon(icon);
                else {
                    let m = L.marker([a.lat, a.lng], {icon: icon}).addTo(map);
                    m.on('click', () => openModal(a.type, a.id));
                    markers[key] = m;
                }
            });
        }

        // --- MODALES ---
        function openModal(type, id) {
            const overlay = document.getElementById('modal-overlay');
            const content = document.getElementById('modal-content');
            overlay.classList.remove('hidden');
            setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); content.classList.add('scale-100', 'opacity-100'); }, 10);
            content.innerHTML = '<div class="p-12 text-center"><i class="fas fa-circle-notch fa-spin text-3xl text-blue-500"></i></div>';
            fetch(`/modal/${type}/${id}`).then(r => r.text()).then(html => {
                content.innerHTML = html;
                if(document.querySelector('.datepicker')) flatpickr(".datepicker", { enableTime: true, dateFormat: "Y-m-d H:i", locale: "es", theme: "dark" });
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
                if(d.length) { document.getElementById('alert-badge').classList.remove('hidden'); d.forEach(a=>c.innerHTML+=`<div class="bg-gray-800 p-3 mb-2 rounded border-l-2 border-red-500 text-sm"><strong class="text-red-400">${a.device}</strong><br><span class="text-gray-300">${a.message}</span></div>`); }
            });
        }

        setInterval(loadAssets, 10000); loadAssets(); loadAlerts();
    </script>
</body>
</html>