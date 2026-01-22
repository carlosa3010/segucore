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
        return view('admin.geofences.create'); // Vista con mapa para dibujar
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'area_points' => 'required' // JSON string de coordenadas [[lat,lng],...]
        ]);

        // Convertir JSON a WKT para Traccar (POLYGON((lat lon, lat lon...)))
        $points = json_decode($request->area_points);
        $wkt = "POLYGON((";
        foreach($points as $p) {
            $wkt .= "{$p->lat} {$p->lng}, ";
        }
        // Cerrar polígono (primer punto al final)
        $wkt .= "{$points[0]->lat} {$points[0]->lng}))";

        // 1. Guardar Local
        $geofence = Geofence::create([
            'name' => $request->name,
            'area' => $wkt
        ]);

        // 2. Crear en Traccar API (Importante para alertas reales)
        try {
            $traccarId = $this->traccarApi->createGeofence($geofence->name, $wkt);
            $geofence->update(['traccar_geofence_id' => $traccarId]);
        } catch (\Exception $e) { 
            // Manejar error silencioso o avisar
        }

        return redirect()->route('admin.geofences.index')->with('success', 'Geocerca creada.');
    }
}