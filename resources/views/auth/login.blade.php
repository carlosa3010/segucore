<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso | SeguSmart Core</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen">
    
    <div class="w-full max-w-md p-8 space-y-6 bg-slate-950 rounded-lg border border-slate-800 shadow-2xl">
        <div class="flex justify-center mb-6">
            <div class="text-3xl font-bold text-white tracking-wider flex items-center gap-2">
                <span class="text-blue-600 text-4xl">●</span> SEGU<span class="text-blue-500">SMART</span>
            </div>
        </div>

        <h2 class="text-center text-xl font-bold text-slate-300">Acceso al Sistema</h2>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-400 mb-1">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                    class="w-full bg-slate-900 border border-slate-700 rounded p-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                    placeholder="admin@segusmart.com">
                @error('email')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-400 mb-1">Contraseña</label>
                <input id="password" type="password" name="password" required 
                    class="w-full bg-slate-900 border border-slate-700 rounded p-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 rounded text-white font-bold transition shadow-lg shadow-blue-900/20">
                Iniciar Sesión &rarr;
            </button>
        </form>

        <p class="text-center text-xs text-slate-600 mt-4">
            &copy; {{ date('Y') }} SeguSmart Security Core v1.0
        </p>
    </div>

</body>
</html>