<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | SeguSmart Ops</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        /* Scrollbar oscura personalizada */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        .leaflet-container { background: #0f172a; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-300 overflow-hidden">
    
    <header class="bg-slate-950 border-b border-slate-800 px-4 py-2 flex justify-between items-center shadow-md z-50">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo-white.png') }}" alt="SeguSmart" class="h-6">
            <span class="bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded animate-pulse">EN VIVO</span>
            <div id="clock" class="font-mono text-blue-400 font-bold text-lg">--:--:--</div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-xs text-right">
                <div class="text-white font-bold">{{ Auth::user()->name ?? 'Operador' }}</div>
                <div class="text-slate-500">Puesto #1</div>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1 rounded text-xs border border-slate-700 transition">
                Salir a Admin
            </a>
        </div>
    </header>

    <main class="flex-1 overflow-hidden relative">
        @yield('content')
    </main>

    <script>
        setInterval(() => {
            document.getElementById('clock').innerText = new Date().toLocaleTimeString();
        }, 1000);
    </script>
    @stack('scripts')
</body>
</html>