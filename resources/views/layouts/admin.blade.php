<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - SeguCore')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --segucore-dark: #0f172a;
            --segucore-panel: #1e293b;
            --segucore-accent: #3b82f6;
            --segucore-green: #C6F211;
        }
        body { background-color: var(--segucore-dark); color: #e2e8f0; font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar Active Link */
        .nav-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 3px solid var(--segucore-green);
            color: white;
        }
        
        /* Inputs oscuros para formularios */
        .form-input {
            background-color: #0f172a;
            border: 1px solid #334155;
            color: white;
            padding: 0.5rem;
            border-radius: 0.375rem;
            width: 100%;
        }
        .form-input:focus { outline: none; border-color: var(--segucore-green); }
        
        /* Scrollbar del menú */
        aside nav::-webkit-scrollbar { width: 4px; }
        aside nav::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }
    </style>
</head>
<body class="antialiased flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    <aside class="w-64 bg-[#111] border-r border-gray-800 flex flex-col hidden md:flex h-full fixed md:relative z-30 transition-transform transform md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        
        <div class="h-20 flex items-center justify-center border-b border-gray-800 py-4 shrink-0">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/logo-white.png') }}" alt="SeguCore" class="h-12 object-contain hover:opacity-80 transition">
            </a>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
            
            <a href="{{ route('admin.dashboard') }}" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="mr-3 text-lg">📊</span> Dashboard
            </a>

            <a href="{{ route('admin.operations.console') }}" target="_blank" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-red-400 hover:bg-red-900/20 hover:text-red-300 border border-transparent hover:border-red-900/50 mt-2">
                <span class="mr-3 text-lg animate-pulse">🚨</span> Consola Operativa
            </a>

            <div class="mt-8">
                <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800 pb-1 mb-2">Cartera de Clientes</h3>
                <a href="{{ route('admin.customers.index') }}" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <span class="mr-3">👥</span> Clientes (Master)
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">🤝</span> CRM / Leads
                </a>
            </div>

            <div class="mt-6">
                <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800 pb-1 mb-2">Monitoreo de Alarmas</h3>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">📟</span> Cuentas & Paneles
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">📈</span> Reportes de Eventos
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-300 cursor-not-allowed opacity-50">
                    <span class="mr-3">📹</span> Videoverificación
                </a>
            </div>

            <div class="mt-6">
                <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800 pb-1 mb-2">Rastreo GPS</h3>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">🛰️</span> Dispositivos GPS
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">🚚</span> Gestión de Flotas
                </a>
            </div>

            <div class="mt-6">
                <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800 pb-1 mb-2">Seguridad Física</h3>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">🚓</span> Patrullas
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">👮</span> Guardias (App)
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-300 cursor-not-allowed opacity-50">
                    <span class="mr-3">🐕</span> Unidad K9 (Unitree)
                </a>
            </div>

            <div class="mt-6">
                <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800 pb-1 mb-2">Administración</h3>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">💰</span> Facturación
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">💳</span> Pagos y Tasas
                </a>
            </div>

            <div class="mt-6 mb-10">
                <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-800 pb-1 mb-2">Configuración</h3>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">⚙️</span> Ajustes Globales
                </a>
                <a href="{{ route('admin.sia-codes.index') }}" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->routeIs('admin.sia-codes.*') ? 'active' : '' }}">
                    <span class="mr-3">📋</span> Códigos SIA
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">🔗</span> Integraciones API
                </a>
                <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                    <span class="mr-3">🛡️</span> Usuarios y Roles
                </a>
            </div>
        </nav>

        <div class="border-t border-gray-800 p-4 bg-black/20 shrink-0">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-white">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white truncate max-w-[140px]">{{ Auth::user()->name ?? 'Administrador' }}</p>
                    <p class="text-[10px] text-gray-500 truncate">En línea</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
        
        <header class="md:hidden bg-[#111] border-b border-gray-800 p-4 flex items-center justify-between shrink-0">
            <span class="font-bold text-white tracking-widest">SEGUCORE</span>
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <main class="flex-1 overflow-y-auto p-6 relative bg-[#0f172a]">
            
            @if(session('success'))
                <div class="mb-4 bg-green-900/30 border border-green-600/50 text-green-200 px-4 py-3 rounded relative shadow-lg backdrop-blur-sm" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        <div>
                            <strong class="font-bold">¡Excelente!</strong>
                            <span class="block sm:inline text-sm">{{ session('success') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-900/30 border border-red-600/50 text-red-200 px-4 py-3 rounded relative shadow-lg backdrop-blur-sm" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⚠️</span>
                        <div>
                            <strong class="font-bold">Error:</strong>
                            <span class="block sm:inline text-sm">{{ session('error') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-900/30 border border-red-600/50 text-red-200 px-4 py-3 rounded relative shadow-lg" role="alert">
                    <strong class="font-bold block mb-1">Por favor corrige los siguientes errores:</strong>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
            
        </main>
    </div>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-20 md:hidden backdrop-blur-sm"></div>

</body>
</html>