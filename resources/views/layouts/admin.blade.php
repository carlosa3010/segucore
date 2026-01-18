<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - SeguCore')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> <style>
        :root {
            --segucore-dark: #0f172a;
            --segucore-panel: #1e293b;
            --segucore-accent: #3b82f6;
            --segucore-green: #C6F211; /* Tu color de marca */
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
    </style>
</head>
<body class="antialiased flex h-screen overflow-hidden">

    <aside class="w-64 bg-[#111] border-r border-gray-800 flex flex-col hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-gray-800">
            <img src="{{ asset('images/logo-white.png') }}" alt="SeguCore" class="h-8 mr-2">
            <span class="font-bold text-lg tracking-wider text-white">SEGUCORE</span>
        </div>

        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Principal</p>
            
            <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                📊 Dashboard
            </a>

            <a href="{{ route('admin.operations.console') }}" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->routeIs('admin.operations.*') ? 'active' : '' }}">
                🚨 Consola Operativa
            </a>

            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Gestión</p>

            <a href="{{ route('admin.customers.index') }}" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                👥 Clientes
            </a>
            
            <a href="#" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                🛡️ Cuentas & Zonas
            </a>

            <a href="#" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                📍 GPS & Flotas
            </a>

            <a href="#" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                🚓 Patrullas
            </a>

            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Administración</p>

            <a href="#" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                💰 Facturación
            </a>
            <a href="#" class="nav-link flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                ⚙️ Configuración
            </a>
        </nav>

        <div class="border-t border-gray-800 p-4">
            <div class="flex items-center">
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">Administrador</p>
                    <p class="text-xs text-gray-500">admin@segucore.com</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <header class="md:hidden bg-[#111] border-b border-gray-800 p-4 flex items-center justify-between">
            <span class="font-bold text-white">SEGUCORE</span>
            <button class="text-gray-300">☰</button>
        </header>

        <main class="flex-1 overflow-y-auto p-6 relative">
            
            @if(session('success'))
                <div class="mb-4 bg-green-900/50 border border-green-600 text-green-200 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-900/50 border border-red-600 text-red-200 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
            
        </main>
    </div>

</body>
</html>