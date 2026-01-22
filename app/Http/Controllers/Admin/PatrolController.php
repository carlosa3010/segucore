<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patrol;
use App\Models\GpsDevice;
use Illuminate\Http\Request;

class PatrolController extends Controller
{
    /**
     * 1. LISTADO DE PATRULLAS
     */
    public function index()
    {
        // Cargamos la relación con GPS y los Guardias asignados actualmente
        $patrols = Patrol::with(['gpsDevice', 'guards'])->orderBy('name')->paginate(10);
        
        return view('admin.patrols.index', compact('patrols'));
    }

    /**
     * 2. FORMULARIO DE CREACIÓN
     */
    public function create()
    {
        // Lógica para el Selector de GPS:
        // Obtener IDs de GPS que ya están asignados a cualquier patrulla ACTIVA
        // para no mostrarlos en la lista y evitar conflictos.
        $assignedGpsIds = Patrol::where('is_active', true)
            ->whereNotNull('gps_device_id')
            ->pluck('gps_device_id');

        // Buscar dispositivos disponibles (excluyendo los asignados)
        $gpsDevices = GpsDevice::whereNotIn('id', $assignedGpsIds)
            ->orderBy('name')
            ->get();
        
        return view('admin.patrols.create', compact('gpsDevices'));
    }

    /**
     * 3. GUARDAR PATRULLA
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'vehicle_type' => 'required|in:car,motorcycle,foot,bicycle',
            'plate_number' => 'nullable|string|max:20',
            // Validamos que el GPS exista y que sea único en la tabla patrols
            'gps_device_id' => 'nullable|exists:gps_devices,id|unique:patrols,gps_device_id',
            'is_active' => 'boolean'
        ]);

        // Default para is_active si no viene (aunque el validate boolean lo maneja, aseguramos)
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Patrol::create($validated);

        return redirect()->route('admin.patrols.index')
            ->with('success', 'Patrulla registrada correctamente.');
    }

    /**
     * 4. FORMULARIO DE EDICIÓN
     */
    public function edit(Patrol $patrol)
    {
        // Obtener GPS ocupados por OTRAS patrullas (excluyendo la actual)
        $assignedGpsIds = Patrol::where('is_active', true)
            ->whereNotNull('gps_device_id')
            ->where('id', '!=', $patrol->id) // Importante: Permitir el propio GPS
            ->pluck('gps_device_id');

        $gpsDevices = GpsDevice::whereNotIn('id', $assignedGpsIds)
            ->orderBy('name')
            ->get();

        return view('admin.patrols.edit', compact('patrol', 'gpsDevices'));
    }

    /**
     * 5. ACTUALIZAR PATRULLA
     */
    public function update(Request $request, Patrol $patrol)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'vehicle_type' => 'required|in:car,motorcycle,foot,bicycle',
            'plate_number' => 'nullable|string|max:20',
            // Unique ignorando el ID actual de la patrulla
            'gps_device_id' => 'nullable|exists:gps_devices,id|unique:patrols,gps_device_id,' . $patrol->id,
            'is_active' => 'boolean'
        ]);

        // Manejo de checkbox HTML (si no está marcado, no se envía)
        $validated['is_active'] = $request->input('is_active', 0);

        $patrol->update($validated);

        return redirect()->route('admin.patrols.index')
            ->with('success', 'Datos de la patrulla actualizados.');
    }

    /**
     * 6. ELIMINAR PATRULLA
     */
    public function destroy(Patrol $patrol)
    {
        // Validación de seguridad: No borrar si tiene guardias "en turno" (on_duty)
        // Asumiendo que la relación 'guards' en el modelo Patrol es hasMany
        if ($patrol->guards()->where('on_duty', true)->exists()) {
            return back()->with('error', 'No se puede eliminar: Hay guardias activos en esta unidad.');
        }

        // Si solo tiene guardias asignados pero NO en turno, se desvinculan (nullOnDelete en migración)
        $patrol->delete();

        return redirect()->route('admin.patrols.index')
            ->with('success', 'Unidad de patrulla eliminada.');
    }
}