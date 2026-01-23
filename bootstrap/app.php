<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // 1. Alias para el Video Wall (Ya lo tenías)
        $middleware->alias([
            'video.wall' => \App\Http\Middleware\EnsureVideoWallAccess::class,
        ]);

        // 2. SOLUCIÓN A TU PROBLEMA: Redirección Inteligente
        $middleware->redirectGuestsTo(function (Request $request) {
            // Si intenta entrar por el dominio de cliente, mándalo al login de cliente
            if ($request->getHost() === 'cliente.segusmart24.com') {
                return route('client.login');
            }
            // Si no, mándalo al login normal (Admin)
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();