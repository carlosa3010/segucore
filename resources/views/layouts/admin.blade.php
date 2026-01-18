<nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
    
    <a href="{{ route('admin.dashboard') }}" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span class="mr-3 text-lg">📊</span> Dashboard
    </a>

    <a href="{{ route('admin.operations.console') }}" target="_blank" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-red-400 hover:bg-red-900/20 hover:text-red-300 border border-transparent hover:border-red-900/50 mt-2">
        <span class="mr-3 text-lg">🚨</span> Consola Operativa
    </a>

    <div class="mt-6">
        <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sección Clientes</h3>
        <a href="{{ route('admin.customers.index') }}" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white mt-1">
            <span class="mr-3">👥</span> Clientes (Master)
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
            <span class="mr-3">🤝</span> CRM / Leads
        </a>
    </div>

    <div class="mt-6">
        <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sección Alarmas</h3>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white mt-1">
            <span class="mr-3">📟</span> Cuentas Monitoreo
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
            <span class="mr-3">📈</span> Reportes Eventos
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-300 cursor-not-allowed" title="Próximamente">
            <span class="mr-3">📹</span> Videoverificación (Hik)
        </a>
    </div>

    <div class="mt-6">
        <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sección GPS</h3>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white mt-1">
            <span class="mr-3">🛰️</span> Cuentas GPS
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
            <span class="mr-3">🚚</span> Flotas & Comandos
        </a>
    </div>

    <div class="mt-6">
        <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Patrullas / Guardias</h3>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white mt-1">
            <span class="mr-3">🚓</span> Gestión Patrullas
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
            <span class="mr-3">👮</span> Guardias (App)
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-300 cursor-not-allowed">
            <span class="mr-3">🐕</span> Perros Unitree
        </a>
    </div>

    <div class="mt-6">
        <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Finanzas</h3>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white mt-1">
            <span class="mr-3">💰</span> Facturación
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-800 hover:text-white">
            <span class="mr-3">💳</span> Pagos
        </a>
    </div>

    <div class="mt-6 mb-10">
        <h3 class="px-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Sistema</h3>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white mt-1">
            <span class="mr-3">⚙️</span> Configuración Global
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
            <span class="mr-3">🔗</span> Integraciones
        </a>
        <a href="#" class="nav-link group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
            <span class="mr-3">🛡️</span> Usuarios y Roles
        </a>
    </div>
</nav>