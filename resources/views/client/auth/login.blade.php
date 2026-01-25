<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Clientes - Segusmart24</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-black flex items-center justify-center h-screen relative overflow-hidden">
    
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-gray-800 via-black to-black opacity-50"></div>
    
    <div class="relative z-10 w-full max-w-sm bg-neutral-900 border border-gray-800 p-8 rounded-xl shadow-2xl">
        
        <div class="text-center mb-8 flex flex-col items-center">
            <img src="{{ asset('images/logo-white.png') }}" alt="SeguCore" class="h-16 mb-4 object-contain opacity-90 drop-shadow-lg">
            <h1 class="text-xl font-bold text-white tracking-wide uppercase">Portal Cliente</h1>
            <p class="text-gray-500 text-xs mt-1">Acceso seguro a su flota y seguridad</p>
        </div>

        <form method="POST" action="{{ route('client.login') }}" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-gray-400 text-xs font-bold mb-2 uppercase tracking-wide">Correo Electrónico</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" 
                           class="w-full pl-10 pr-4 py-3 rounded bg-gray-800 text-white border border-gray-700 focus:border-white focus:ring-0 focus:outline-none transition placeholder-gray-600 text-sm" 
                           placeholder="usuario@empresa.com" required autofocus>
                </div>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-400 text-xs font-bold mb-2 uppercase tracking-wide">Contraseña</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" 
                           class="w-full pl-10 pr-4 py-3 rounded bg-gray-800 text-white border border-gray-700 focus:border-white focus:ring-0 focus:outline-none transition placeholder-gray-600 text-sm" 
                           placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-white hover:bg-gray-200 text-black font-bold py-3 rounded transition duration-200 uppercase text-xs tracking-widest shadow-lg transform hover:scale-[1.02]">
                Iniciar Sesión
            </button>
        </form>
        
        <div class="mt-8 text-center border-t border-gray-800 pt-6">
            <p class="text-[10px] text-gray-600">
                <i class="fas fa-headset mr-1"></i> Soporte 24/7: <span class="text-gray-400 font-bold">0800-SEGU-24</span>
            </p>
        </div>
    </div>

    <div class="absolute bottom-4 text-center w-full">
        <p class="text-[10px] text-gray-700">&copy; {{ date('Y') }} SeguSmart24. Todos los derechos reservados.</p>
    </div>
</body>
</html>