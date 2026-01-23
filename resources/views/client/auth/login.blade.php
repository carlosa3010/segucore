<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Clientes - Segusmart24</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen bg-[url('/images/map-bg.jpg')] bg-cover bg-center bg-no-repeat">
    <div class="absolute inset-0 bg-black/70"></div> <div class="relative z-10 w-full max-w-md bg-gray-800/90 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white tracking-wider">SEGUSMART<span class="text-blue-500">24</span></h1>
            <p class="text-gray-400 text-sm mt-2">Portal de Autogestión y Monitoreo</p>
        </div>

        <form method="POST" action="{{ route('client.login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-300 text-sm font-bold mb-2">Correo Electrónico</label>
                <input type="email" name="email" class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-blue-500 focus:outline-none" placeholder="cliente@ejemplo.com" required autofocus>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-300 text-sm font-bold mb-2">Contraseña</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-blue-500 focus:outline-none" placeholder="••••••••" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg transition duration-300 shadow-lg shadow-blue-500/50">
                Ingresar al Panel
            </button>
        </form>
        
        <div class="mt-6 text-center border-t border-gray-700 pt-4">
            <p class="text-xs text-gray-500">¿Necesitas soporte? Llama al 0800-SEGU-24</p>
        </div>
    </div>
</body>
</html>