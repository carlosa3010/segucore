<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <title>SeguSmart | Satélite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { background-color: #000; overflow: hidden; font-family: 'Consolas', monospace; }
        
        /* HUD Styles */
        .hud-box { background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(5px); border: 1px solid #444; }
        .neon-text { color: #C6F211; text-shadow: 0 0 5px rgba(198, 242, 17, 0.5); }
        
        /* Animación de Alarma */
        .critical-border { animation: border-flash 1s infinite; border-color: red !important; background: rgba(255, 0, 0, 0.2); }
        @keyframes border-flash { 
            0% { box-shadow: 0 0 0 red; } 
            50% { box-shadow: 0 0 20px red; } 
            100% { box-shadow: 0 0 0 red; } 
        }
        
        /* Marcador Pulsante en el Mapa */
        .pulse-icon {
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulse-ring 1.5s infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body class="h-screen w-screen relative">

    <div id="map" class="absolute inset-0 z-0"></div>

    <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(circle, transparent 60%, black 100%);"></div>

    <div id="status-box" class="absolute top-4 left-4 z-10 hud-box p-4 rounded w-96 transition-all duration-300">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-gray-400 text-[10px] uppercase tracking-[0.2em]">ESTADO DEL SISTEMA</h2>
                <div id="last-event" class="text-white text-2xl font-bold mt-1">ESCANEO ACTIVO</div>
                <div id="last-client" class="neon-text text-sm mt-1">Sin alertas recientes</div>
            </div>
            <div id="status-icon" class="text-4xl">🛰️</div>
        </div>
    </div>

    <div class="absolute bottom-6 right-6 z-10 text-right pointer-events-none drop-shadow-md">
        <h1 id="clock" class="text-5xl font-bold text-white tracking-widest">00:00</h1>
        <p class="text-gray-300 text-xs tracking-[0.5em] mt-2">VISTA SATELITAL EN VIVO</p>
    </div>

    <script>
        // 1. INICIAR MAPA SATELITAL (Esri World Imagery)
        const map = L.map('map', { zoomControl: false }).setView([10.06, -69.33], 13);

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Esri Satellite',
            maxZoom: 18
        }).addTo(map);

        // Capa de etiquetas (Calles transparentes encima del satélite) - Opcional para leer mejor
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}{r}.png', {
            maxZoom: 18,
            opacity: 0.8
        }).addTo(map);

        // Grupo de marcadores (Para poder borrarlos luego)
        let markersLayer = L.layerGroup().addTo(map);

        // 2. LÓGICA DE DATOS
        let lastId = 0;
        
        async function syncMap() {
            try {
                const res = await fetch('/api/live-events');
                const events = await res.json();

                if(events.length === 0) return;
                const latest = events[0];

                // Solo actuar si es nuevo
                if(latest.id > lastId) {
                    lastId = latest.id;
                    updateScreen(latest);
                }
            } catch(e) { console.log("Conectando..."); }
        }

        function updateScreen(evt) {
            // Actualizar Textos
            document.getElementById('last-event').innerText = evt.description;
            document.getElementById('last-client').innerText = evt.customer_name;
            document.getElementById('status-icon').innerText = evt.status_icon;
            
            const box = document.getElementById('status-box');

            // Si es alarma (Prioridad 4 o 5)
            if(evt.priority >= 4) {
                box.classList.add('critical-border'); // Borde rojo parpadeante
                
                // Limpiar mapa y poner marcador nuevo
                markersLayer.clearLayers();
                
                // Crear icono pulsante CSS
                const pulseIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: "<div class='pulse-icon' style='width: 20px; height: 20px;'></div>",
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                // Añadir marcador (Simulando coordenadas aleatorias en BQTO por ahora)
                // En el futuro usaremos: [evt.lat, evt.lng]
                const lat = 10.06 + (Math.random() * 0.02 - 0.01);
                const lng = -69.33 + (Math.random() * 0.02 - 0.01);
                
                L.marker([lat, lng], {icon: pulseIcon}).addTo(markersLayer);
                
                // Mover la cámara a la alerta suavemente
                map.flyTo([lat, lng], 15, { duration: 2 });

            } else {
                box.classList.remove('critical-border');
            }
        }

        setInterval(() => document.getElementById('clock').innerText = new Date().toLocaleTimeString('en-US',{hour12:false}), 1000);
        setInterval(syncMap, 2000);
    </script>
</body>
</html>