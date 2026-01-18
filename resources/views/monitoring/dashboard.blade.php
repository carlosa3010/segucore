@extends('layouts.app', ['fullscreen' => true])

@section('title', 'SeguSmart | Dashboard')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Fondo Negro Puro para Video Wall */
        body { 
            background-color: #000000; 
            color: #ffffff; 
            overflow: hidden; /* Evita scrollbars dobles */
        }
        
        /* Colores de Marca Específicos del Dashboard */
        .neon-text { color: #C6F211; }
        .border-neon { border-color: #C6F211; }
        
        /* Fuente monoespaciada para datos numéricos */
        .font-data { font-family: 'Consolas', 'Monaco', monospace; }

        /* Scroll invisible pero funcional */
        ::-webkit-scrollbar { width: 0px; }

        /* Animación para alarmas críticas (Parpadeo Rojo) */
        @keyframes flash-alert {
            0% { background-color: rgba(220, 38, 38, 0.4); }
            50% { background-color: rgba(220, 38, 38, 0.1); }
            100% { background-color: rgba(220, 38, 38, 0.4); }
        }
        .animate-flash { animation: flash-alert 1.5s infinite; }
    </style>
@endpush

@section('content')
    <div class="h-screen w-screen flex flex-col bg-black">

        <header class="flex items-center justify-between px-6 py-4 border-b border-gray-800 bg-[#050505]">
            <div class="flex items-center gap-6">
                <img src="{{ asset('images/logo-white.png') }}" alt="SEGUSMART" class="h-10 object-contain opacity-90">
                
                <div class="flex items-center gap-2 px-3 py-1 rounded border border-[#333] bg-[#111]">
                    <div class="w-2 h-2 rounded-full bg-[#C6F211] animate-pulse"></div>
                    <span class="text-xs font-bold tracking-widest text-gray-400">EN VIVO</span>
                </div>
            </div>

            <div class="text-right">
                <h1 id="clock" class="text-4xl font-bold text-white tracking-wider font-data">00:00:00</h1>
                <p id="date" class="text-xs text-gray-500 uppercase tracking-[0.2em] mt-1">CARGANDO...</p>
            </div>
        </header>

        <div class="flex px-4 py-2 bg-[#0a0a0a] border-b border-[#222] text-xs uppercase tracking-widest text-gray-600 font-bold">
            <div class="w-28 pl-2">Hora</div>
            <div class="w-32">Cuenta</div>
            <div class="flex-1">Evento / Cliente</div>
            <div class="w-24 text-right pr-2">Zona</div>
        </div>

        <div id="event-grid" class="flex-1 overflow-y-auto p-0 scroll-smooth relative">
            <div class="flex flex-col items-center justify-center h-full text-gray-700 space-y-4 absolute inset-0">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#C6F211]"></div>
                <p class="text-sm tracking-widest uppercase">Esperando eventos...</p>
            </div>
        </div>

        <div class="h-8 bg-[#050505] border-t border-[#222] flex items-center justify-between px-6 text-xs text-gray-500">
            <span id="connection-status" class="text-green-500 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500"></span> CONECTADO
            </span>
            <span id="total-events">REGISTROS: 0</span>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // --- 1. RELOJ Y FECHA ---
        function updateTime() {
            const now = new Date();
            // Formato de hora 24h
            document.getElementById('clock').innerText = now.toLocaleTimeString('en-US', {hour12: false});
            // Formato de fecha completo en español
            document.getElementById('date').innerText = now.toLocaleDateString('es-VE', { weekday: 'long', day: 'numeric', month: 'long' }).toUpperCase();
        }
        // Actualizar cada segundo e inmediatamente al cargar
        setInterval(updateTime, 1000);
        updateTime();

        // --- 2. LÓGICA DE EVENTOS (AJAX polling) ---
        async function fetchEvents() {
            try {
                // Llamada a la API creada en MonitoringController
                const res = await fetch("{{ route('api.live-events') }}");
                if (!res.ok) throw new Error('Error en API');

                const events = await res.json();
                
                const grid = document.getElementById('event-grid');
                document.getElementById('total-events').innerText = "REGISTROS: " + events.length;

                // Si no hay eventos, no hacemos nada (mantenemos el estado anterior o el loading inicial si es la primera carga)
                if (events.length === 0 && grid.children.length > 1) return; 
                // Nota: Podríamos agregar lógica para mostrar "Sin eventos" si la lista se vacía.

                let html = '';
                events.forEach(evt => {
                    // CONFIGURACIÓN DE ESTILOS SEGÚN PRIORIDAD
                    
                    let rowClass = "border-b border-[#1a1a1a] hover:bg-[#111] transition-colors bg-[#050505]";
                    let timeColor = "text-gray-400";
                    let accountColor = "text-gray-500";
                    let descColor = "text-white";
                    let subDescColor = "text-gray-500";
                    let icon = "";

                    // PRIORIDAD 5: FUEGO / PANICO (Fondo Rojo)
                    if (evt.priority >= 5) {
                        rowClass = "bg-red-900/20 border-b border-red-900 animate-flash";
                        timeColor = "text-white font-bold";
                        accountColor = "text-red-200";
                        descColor = "text-red-100 font-bold";
                        subDescColor = "text-red-300";
                        icon = "🔥";
                    } 
                    // PRIORIDAD 4: ROBO (Borde Naranja)
                    else if (evt.priority == 4) {
                        rowClass = "bg-orange-900/10 border-l-4 border-l-orange-500 border-b border-[#222]";
                        descColor = "text-orange-400";
                        subDescColor = "text-orange-200/70";
                        icon = "🚨";
                    }
                    // PRIORIDAD 2: TÉCNICO (Amarillo)
                    else if (evt.priority == 2) {
                        descColor = "text-yellow-400";
                        rowClass = "border-b border-[#222]";
                        icon = "⚠️";
                    }
                    // APERTURA / CIERRE (Verde Neón / Gris)
                    else if (evt.code == 'OP' || evt.code == 'CL') {
                        descColor = "text-[#C6F211]"; 
                        rowClass = "border-b border-[#111] opacity-60"; 
                        icon = evt.code == 'OP' ? '🔓' : '🔒';
                    }

                    // Renderizado de la fila
                    html += `
                        <div class="flex items-center py-4 px-4 ${rowClass} cursor-default group">
                            
                            <div class="w-28 font-data text-xl ${timeColor}">
                                ${evt.time_raw}
                            </div>

                            <div class="w-32 font-data text-sm font-bold tracking-wide ${accountColor}">
                                ${evt.account}
                            </div>

                            <div class="flex-1 flex flex-col justify-center min-w-0 px-4">
                                <div class="text-2xl font-bold tracking-tight truncate flex items-center gap-2 ${descColor}">
                                    <span>${icon}</span> ${evt.description}
                                </div>
                                <div class="text-sm uppercase tracking-wide truncate ${subDescColor}">
                                    ${evt.customer_name}
                                </div>
                            </div>

                            <div class="w-24 text-right">
                                <span class="font-data text-xs bg-[#222] text-gray-300 px-2 py-1 rounded border border-[#333] group-hover:border-gray-500">
                                    ZN ${evt.zone}
                                </span>
                            </div>
                        </div>
                    `;
                });
                
                // Actualizar el DOM
                if (html) grid.innerHTML = html;

                // Restaurar estado de conexión
                const statusEl = document.getElementById('connection-status');
                statusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500"></span> CONECTADO';
                statusEl.className = "text-green-500 flex items-center gap-2";

            } catch (e) {
                console.error("Error fetching events:", e);
                const statusEl = document.getElementById('connection-status');
                statusEl.innerHTML = '🔴 DESCONECTADO';
                statusEl.className = "text-red-500 font-bold animate-pulse";
            }
        }

        // Ejecutar polling cada 2 segundos
        setInterval(fetchEvents, 2000);
        fetchEvents();
    </script>
@endpush