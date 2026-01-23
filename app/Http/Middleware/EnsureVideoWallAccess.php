<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVideoWallAccess
{
    /**
     * Protege el acceso a los paneles de visualización (Video Wall).
     * Requiere un token en la URL (?key=...) o en el Header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define esta clave en tu archivo .env
        $validKey = env('VIDEO_WALL_KEY');

        if (!$validKey) {
            // Por seguridad, si no hay clave configurada, bloqueamos todo.
            abort(500, 'Error de configuración: VIDEO_WALL_KEY no definida en el servidor.');
        }

        // 1. Verificación por URL (para navegadores en modo Kiosco)
        if ($request->query('key') === $validKey) {
            return $next($request);
        }

        // 2. Verificación por Header (para futuras integraciones API/Pantallas Smart)
        if ($request->header('X-Video-Wall-Key') === $validKey) {
            return $next($request);
        }

        // 3. Permitir acceso si es un Administrador ya logueado (Opcional, útil para pruebas)
        if ($request->user() && $request->user()->email === 'admin@segusmart24.com') { // O verifica rol
             return $next($request);
        }

        abort(403, 'ACCESO DENEGADO: Credenciales de Video Wall inválidas.');
    }
}