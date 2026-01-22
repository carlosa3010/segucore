<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SeguCore Admin') - Segusmart 24</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 text-slate-200">
    
    <div class="flex h-screen overflow-hidden">
        
        <aside class="w-64 bg-slate-950 border-r border-slate-800 flex flex-col shrink-0 transition-all duration-300">
            <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950">
                <div class="flex items-center gap-3 font-bold text-xl tracking-wider text-white">
                    <span class="text-blue-600 text-2xl">🛡️</span> SEGUCORE
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                   {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span>📊</span> Dashboard
                </a>

                <div class="pt-4 pb-1 pl-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Operaciones</div>

                <a href="{{ route('admin.operations.console') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                   {{ request()->routeIs('admin.operations.*') ? 'bg-red-600 text-white shadow-lg shadow-red-900/50 animate-pulse-slow' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span>🚨</span> Consola Activa
                </a>

                <div class="pt-4 pb-1 pl-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gestión</div>

                <a href="{{ route('admin.customers.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                   {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.accounts.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span>👥</span> Clientes y Cuentas
                </a>

                <a href="{{ route('admin.reports.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                   {{ request()->routeIs('admin.reports.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span>📑</span> Reportes Históricos
                </a>

                <div class="pt-4 pb-1 pl-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Administración</div>

                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                   {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span>👮</span> Operadores
                </a>

                <div x-data="{ open: {{ request()->routeIs('admin.sia-codes.*') || request()->routeIs('admin.config.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                        <div class="flex items-center gap-3">
                            <span>⚙️</span> Configuración
                        </div>
                        <span :class="open ? 'rotate-90' : ''" class="transition-transform text-[10px]">▶</span>
                    </button>
                    
                    <div x-show="open" x-cloak class="pl-9 space-y-1 mt-1">
                        <a href="{{ route('admin.sia-codes.index') }}" 
                           class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                           {{ request()->routeIs('admin.sia-codes.*') ? 'text-blue-400 bg-slate-800/50' : 'text-slate-500 hover:text-white' }}">
                           • Códigos SIA
                        </a>
                        
                        <a href="{{ route('admin.config.resolutions.index') }}" 
                           class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                           {{ request()->routeIs('admin.config.resolutions.*') || request()->routeIs('admin.config.hold-reasons.*') ? 'text-blue-400 bg-slate-800/50' : 'text-slate-500 hover:text-white' }}">
                           • Incidentes y Cierre
                        </a>

                        <a href="{{ route('admin.config.plans.index') }}" 
                           class="block px-3 py-1.5 rounded-md text-xs font-medium transition-colors
                           {{ request()->routeIs('admin.config.plans.*') ? 'text-blue-400 bg-slate-800/50' : 'text-slate-500 hover:text-white' }}">
                           • Planes de Facturación
                        </a>
                    </div>
                </div>

            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-950">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-xs text-white">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-slate-500 truncate">En línea</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-400 transition" title="Cerrar Sesión">
                            🚪
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-900 relative">
            
            <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-4 shrink-0 lg:hidden">
                <span class="font-bold text-white">SEGUCORE</span>
                <button class="text-white">☰</button>
            </header>

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                     class="absolute top-4 right-4 z-50 bg-green-600 text-white px-4 py-2 rounded shadow-lg flex items-center gap-2 text-sm font-bold animate-fade-in-down">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" 
                     class="absolute top-4 right-4 z-50 bg-red-600 text-white px-4 py-2 rounded shadow-lg flex items-center gap-2 text-sm font-bold animate-pulse">
                    <span>⚠️</span> {{ session('error') }}
                    <button @click="show = false" class="ml-2 opacity-50 hover:opacity-100">✕</button>
                </div>
            @endif

            <div class="flex-1 overflow-auto p-0">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>