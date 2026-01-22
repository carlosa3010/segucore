<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guard;
use App\Models\Incident;
use App\Models\PatrolRound;
use Illuminate\Support\Facades\Auth;

class GuardAppController extends Controller
{
    // Helper para obtener al guardia autenticado
    private function getGuard()
    {
        return Guard::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * 1. INICIO DE TURNO / ESTADO
     */
    public function status()
    {
        $guard = $this->getGuard()->load('patrol');
        return response()->json([
            'guard' => $guard,
            'assigned_patrol' => $guard->patrol,
            'active_incidents' => Incident::where('assigned_patrol_id', $guard->current_patrol_id)
                                          ->where('status', 'open')->count()
        ]);
    }

    public function toggleDuty(Request $request)
    {
        $guard = $this->getGuard();
        $guard->update(['on_duty' => $request->on_duty]);
        return response()->json(['message' => 'Estado actualizado', 'on_duty' => $guard->on_duty]);
    }

    /**
     * 2. BOTÓN DE PÁNICO
     */
    public function panic(Request $request)
    {
        $guard = $this->getGuard();
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // Crear incidente crítico
        $incident = Incident::create([
            'customer_id' => null, // Es interno
            'priority' => 'critical',
            'type' => 'panic_button',
            'description' => "PANICO: Guardia {$guard->full_name} solicitó ayuda.",
            'status' => 'open',
            'location_lat' => $lat,
            'location_lng' => $lng,
            'notes' => 'Generado desde App Móvil'
        ]);

        // TODO: Disparar notificación Websocket a la consola

        return response()->json(['success' => true, 'incident_id' => $incident->id]);
    }

    /**
     * 3. ACTUALIZACIÓN GPS (Tracking de la App)
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'battery' => 'nullable|integer'
        ]);

        $guard = $this->getGuard();
        
        $guard->update([
            'last_lat' => $request->lat,
            'last_lng' => $request->lng,
            'battery_level' => $request->battery,
            'last_seen_at' => now()
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * 4. GESTIÓN DE RONDAS
     */
    public function rounds()
    {
        // Devolver rondas disponibles
        return response()->json(PatrolRound::where('is_active', true)->get());
    }

    /**
     * 5. ATENDER INCIDENTE (Navegación)
     */
    public function myIncidents()
    {
        $guard = $this->getGuard();
        if (!$guard->current_patrol_id) return response()->json([]);

        // Buscar incidentes asignados a su patrulla
        // Nota: Necesitarías agregar 'assigned_patrol_id' a la tabla incidents si no existe
        $incidents = Incident::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get(); // Filtrar lógica más compleja aquí

        return response()->json($incidents);
    }
}