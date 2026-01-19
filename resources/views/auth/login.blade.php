<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso | SeguSmart Core</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center bg-slate-950">
    
    <div class="w-full max-w-md p-8 bg-slate-900 rounded-lg border border-slate-800 shadow-2xl relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-blue-400 to-blue-600"></div>

        <div class="flex flex-col items-center justify-center mb-8">
            <img src="{{ asset('images/logo-white.png') }}" alt="SeguSmart" class="h-16 w-auto mb-4 drop-shadow-lg transition transform hover:scale-105">
            <h2 class="text-2xl font-bold text-white tracking-tight">Bienvenido a <span class="text-blue-500">Core</span></h2>
            <p class="text-slate-400 text-sm mt-2">Introduce tus credenciales de operador</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Usuario / Correo</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                        class="w-full bg-slate-950 border border-slate-700 rounded-md py-3 pl-10 pr-3 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition sm:text-sm"
                        placeholder="admin@segusmart.com">
                </div>
                @error('email')
                    <span class="text-red-500 text-xs mt-1 font-bold flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" type="password" name="password" required 
                        class="w-full bg-slate-950 border border-slate-700 rounded-md py-3 pl-10 pr-3 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition sm:text-sm"
                        placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="group w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-blue-500 transition shadow-lg shadow-blue-900/30">
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-blue-400 group-hover:text-blue-300 transition" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                INICIAR SESIÓN
            </button>
        </form>

        <div class="mt-6 border-t border-slate-800 pt-4 text-center">
            <p class="text-xs text-slate-600">
                &copy; {{ date('Y') }} SeguSmart Security Core v1.0
            </p>
        </div>
    </div>
</body>
</html>