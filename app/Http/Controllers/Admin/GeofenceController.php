<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Geofence;
use App\Services\TraccarApiService;
use Illuminate\Http\Request;

class GeofenceController extends Controller
{
    protected $traccarApi;

    public function __construct(TraccarApiService $traccarApi)
    {
        $this->traccarApi = $traccarApi;
    }

    public function index()
    {
        $geofences = Geofence::paginate(10);
        return view('admin.geofences.index', compact('geofences'));
    }

    public function create()
    {
        return view('admin.geofences.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'area_points' => 'required' // JSON string de coordenadas
        ]);

        // Convertir puntos a formato WKT
        $wkt = $this->jsonToWkt($request->area_points);

        // 1. Guardar Local
        $geofence = Geofence::create([
            'name' => $request->name,
            'description' => $request->description,
            'area' => $wkt
        ]);

        // 2. Crear en Traccar API
        try {
            $traccarId = $this->traccarApi->createGeofence($geofence->name, $wkt, $geofence->description);
            if ($traccarId) {
                $geofence->update(['traccar_geofence_id' => $traccarId]);
            }
        } catch (\Exception $e) { 
            // Continuar si falla la API
        }

        return redirect()->route('admin.geofences.index')->with('success', 'Geocerca creada correctamente.');
    }

    public function edit($id)
    {
        $geofence = Geofence::findOrFail($id);
        
        // Convertir WKT almacenado a JSON para que el mapa (Leaflet) lo pueda dibujar
        $polygon = [];
        if ($geofence->area) {
            // Limpiar string "POLYGON((" y "))" para extraer solo números
            $str = str_replace(['POLYGON((', '))'], '', $geofence->area);
            $pairs = explode(',', $str);
            
            foreach ($pairs as $pair) {
                $coords = explode(' ', trim($pair));
                if(count($coords) >= 2) {
                    $polygon[] = ['lat' => (float)$coords[0], 'lng' => (float)$coords[1]];
                }
            }
            // Eliminar el último punto (cierre) para que no se duplique en el editor visual
            array_pop($polygon); 
        }

        return view('admin.geofences.edit', compact('geofence', 'polygon'));
    }

    public function update(Request $request, $id)
    {
        $geofence = Geofence::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'area_points' => 'required'
        ]);

        $wkt = $this->jsonToWkt($request->area_points);

        // Actualizar Local
        $geofence->update([
            'name' => $request->name,
            'description' => $request->description,
            'area' => $wkt
        ]);

        // Actualizar Traccar
        if ($geofence->traccar_geofence_id) {
            try {
                $this->traccarApi->updateGeofence(
                    $geofence->traccar_geofence_id, 
                    $geofence->name, 
                    $wkt, 
                    $geofence->description
                );
            } catch (\Exception $e) { }
        }

        return redirect()->route('admin.geofences.index')->with('success', 'Geocerca actualizada.');
    }

    public function destroy($id)
    {
        $geofence = Geofence::findOrFail($id);

        // Eliminar de Traccar
        if ($geofence->traccar_geofence_id) {
            try {
                $this->traccarApi->deleteGeofence($geofence->traccar_geofence_id);
            } catch (\Exception $e) { }
        }

        // Eliminar Local
        $geofence->delete();

        return redirect()->route('admin.geofences.index')->with('success', 'Geocerca eliminada.');
    }

    /**
     * Función auxiliar para convertir JSON de Leaflet a WKT de Traccar
     */
    private function jsonToWkt($jsonPoints)
    {
        $points = json_decode($jsonPoints);
        
        if (!is_array($points) || empty($points)) return null;

        $wkt = "POLYGON((";
        foreach($points as $p) {
            $wkt .= "{$p->lat} {$p->lng}, ";
        }
        // Cerrar polígono (primer punto al final)
        $wkt .= "{$points[0]->lat} {$points[0]->lng}))";
        
        return $wkt;
    }
}