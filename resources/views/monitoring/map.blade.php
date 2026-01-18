@extends('layouts.app', ['fullscreen' => true])

@section('title', 'SeguCore | Satélite Táctico')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /* Estilos específicos del Mapa */
        body { background-color: #000; overflow: hidden; }
        
        /* HUD (Heads-Up Display) - Estilo Cristal */
        .hud-box { 
            background: rgba(15, 23, 42, 0.85); 
            backdrop-filter: blur(8px); 
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }

        .neon-text { color: #C6F211; text-shadow: 0 0 8px rgba(198, 242, 17, 0.4); }
        .text-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        
        /* Animación de Borde Crítico (Alarma) */
        .critical-alert { 
            animation: border-flash 1s infinite; 
            border: 1px solid #ef4444 !important; 
            background: rgba(127, 29, 29, 0.9);
        }

        @keyframes border-flash { 
            0% { box-shadow: 0 0 0 rgba(239, 68, 68, 0); } 
            50% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.6); } 
            100% { box-shadow: 0 0 0 rgba(239, 68, 68, 0); } 
        }
        
        /* Marcador Pulsante en el Mapa (Sonar) */
        .pulse-icon {
            position: relative;
            background-color: #ef4444;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulse-ring 1.5s infinite;
        }
        
        /* Onda expansiva */
        @keyframes pulse-ring {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
@endpush

@section('content')
    <div class="h-screen w-screen relative bg-black">
        
        <div id="map" class="absolute inset-0 z-0"></div>

        <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(circle, transparent 50%, rgba(0,0,0,0.8) 100%); z-index: 5;"></div>

        <div id="status-box" class="absolute top-6 left-6 z-10 hud-box p-5 rounded-lg w-96 transition-all duration-300 border-l-4 border-l-[#C6F211]">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-gray-400 text-[10px] uppercase tracking-[0.25em] font-bold mb-1">MONITOREO SATELITAL</h2>
                    <div id="last-event" class="text-white text-2xl font-bold leading-tight">ESCANEO ACTIVO</div>
                    <div id="last-client" class="neon-text text-sm mt-1 font-mono tracking-wide">Sin alertas de prioridad</div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        SISTEMA OPERATIVO
                    </div>
                </div>
                <div id="status-icon" class="text-5xl filter drop-shadow-lg">🛰️</div>
            </div>
        </div>

        <div class="absolute bottom-8 right-8 z-10 text-right pointer-events-none text-shadow">
            <h1 id="clock" class="text-6xl font-bold text-white tracking-widest font-mono">00:00</h1>
            <p class="text-[#C6F211] text-xs tracking-[0.6em] mt-2 uppercase font-bold opacity-80">SeguCore Tactical View</p>
        </div>

        <div id="audio-overlay" class="absolute inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm cursor-pointer" onclick="enableAudio()">
            <div class="text-center group">
                <div class="text-6xl mb-4 group-hover:scale-110 transition-transform">🔇</div>
                <h3 class="text-white text-xl tracking-widest font-bold">CLICK PARA ACTIVAR SONIDO</h3>
                <p class="text-gray-400 text-sm mt-2">El navegador requiere interacción para reproducir alertas.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // --- CONFIGURACIÓN DE SONIDO ---
        // Intentamos cargar el sonido local, si falla usamos uno de internet
        let alertAudio = new Audio("{{ asset('sounds/alert.mp3') }}");
        alertAudio.onerror = () => {
            // Sonido de respaldo (Sonar)
            alertAudio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        };
        
        let audioEnabled = false;

        function enableAudio() {
            audioEnabled = true;
            document.getElementById('audio-overlay').style.display = 'none';
            // Reproducir silencio para desbloquear el audio context
            alertAudio.play().then(() => {
                alertAudio.pause();
                alertAudio.currentTime = 0;
            }).catch(e => console.log("Audio waiting..."));
        }

        // --- 1. INICIALIZAR MAPA ---
        // Coordenadas iniciales (Barquisimeto)
        const map = L.map('map', { 
            zoomControl: false, // Ocultar controles para Video Wall
            attributionControl: false 
        }).setView([10.06, -69.33], 13);

        // Capa Satelital (Esri World Imagery)
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19
        }).addTo(map);

        // Capa de Etiquetas (Calles Híbridas)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            opacity: 0.7,
            subdomains: 'abcd'
        }).addTo(map);

        // Capa para los marcadores
        let markersLayer = L.layerGroup().addTo(map);

        // --- 2. LOGICA DE SINCRONIZACIÓN ---
        let lastId = 0;

        async function syncMap() {
            try {
                // Consultamos la API creada en MonitoringController
                const res = await fetch("{{ route('api.live-events') }}");
                const events = await res.json();

                if(events.length === 0) return;
                
                // Tomamos el evento más reciente (Asumiendo que la API devuelve ordenado por prioridad/fecha)
                const latest = events[0];

                // Si detectamos un ID nuevo que no hemos procesado
                if(latest.id > lastId) {
                    lastId = latest.id;
                    handleNewEvent(latest);
                }
            } catch(e) { 
                console.error("Error sync map:", e); 
            }
        }

        function handleNewEvent(evt) {
            // 1. Actualizar HUD
            const box = document.getElementById('status-box');
            document.getElementById('last-event').innerText = evt.description;
            document.getElementById('last-client').innerText = evt.customer_name;
            document.getElementById('status-icon').innerText = evt.status_icon || '⚠️';

            // 2. Gestionar Prioridad (Audio y Color)
            if(evt.priority >= 4) {
                // Modo Alarma
                box.classList.add('critical-alert');
                box.classList.remove('border-l-[#C6F211]');
                box.classList.add('border-l-red-500');
                
                // Reproducir sonido si está habilitado
                if(audioEnabled) {
                    alertAudio.currentTime = 0;
                    alertAudio.play().catch(e => console.error(e));
                }

                // 3. Ubicación en Mapa
                markersLayer.clearLayers();

                // COORDENADAS: 
                // Aquí debes reemplazar los randoms con: evt.latitude y evt.longitude cuando la API los tenga
                // Simulamos una ubicación en Barquisimeto con pequeña variación
                const lat = 10.06 + (Math.random() * 0.03 - 0.015);
                const lng = -69.33 + (Math.random() * 0.03 - 0.015);

                // Crear icono HTML personalizado
                const pulseIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class='pulse-icon'></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10] // Centrar
                });

                // Agregar marcador
                L.marker([lat, lng], {icon: pulseIcon}).addTo(markersLayer);

                // 4. Efecto de Acercamiento (FlyTo)
                map.flyTo([lat, lng], 16, {
                    animate: true,
                    duration: 3 // Duración en segundos del vuelo (efecto cinematográfico)
                });

            } else {
                // Modo Normal
                box.classList.remove('critical-alert');
                box.classList.add('border-l-[#C6F211]');
                box.classList.remove('border-l-red-500');
            }
        }

        // --- 3. RELOJ ---
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('en-US', {hour12: false, hour: '2-digit', minute:'2-digit'});
        }, 1000);

        // --- 4. POLLING ---
        setInterval(syncMap, 2000); // Revisar cada 2 segundos

    </script>
@endpush