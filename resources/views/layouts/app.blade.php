<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SeguCore Platform')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')

    <style>
        /* Variables de Identidad SeguCore */
        :root {
            --segucore-dark: #0f172a;  /* Fondo Oscuro Video Wall */
            --segucore-red: #dc2626;   /* Alarma Crítica */
            --segucore-accent: #3b82f6; /* Botones/Enlaces */
        }
        body {
            background-color: var(--segucore-dark);
            color: #e2e8f0;
            font-family: 'Segoe UI', sans-serif;
        }
    </style>
</head>
<body class="antialiased">
    
    @unless(isset($fullscreen) && $fullscreen)
    <nav class="navbar navbar-dark bg-black border-bottom border-secondary px-4 py-2 flex justify-between items-center">
        <a class="navbar-brand flex items-center gap-3" href="{{ url('/') }}">
            <img src="{{ asset('images/logo-white.png') }}" alt="SeguCore Logo" height="40" class="d-inline-block align-text-top">
            <span class="font-bold text-xl tracking-wider">SEGUCORE</span>
        </a>

        <div class="flex gap-4">
            <a href="{{ route('monitor.index') }}" class="text-gray-300 hover:text-white">🖥️ Monitor</a>
            <a href="{{ route('monitor.map') }}" class="text-gray-300 hover:text-white">🗺️ Mapa</a>
            <a href="#" class="text-gray-300 hover:text-white">⚙️ Admin</a>
        </div>
    </nav>
    @endunless

    <main class="@if(!isset($fullscreen)) container mx-auto py-4 @endif">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>