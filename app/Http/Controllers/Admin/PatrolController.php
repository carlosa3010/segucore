<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patrol;
use App\Models\GpsDevice;
use Illuminate\Http\Request;

class PatrolController extends Controller
{
    public function index()
    {
        $patrols = Patrol::with('gpsDevice', 'guards')->get();
        return view('admin.patrols.index', compact('patrols'));
    }

    public function create()
    {
        // Solo mostrar GPS que NO estén asignados a otra patrulla
        $assignedGpsIds = Patrol::whereNotNull('gps_device_id')->pluck('gps_device_id');
        $gpsDevices = GpsDevice::whereNotIn('id', $assignedGpsIds)->get();
        
        return view('admin.patrols.create', compact('gpsDevices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'gps_device_id' => 'nullable|exists:gps_devices,id',
        ]);

        Patrol::create($request->all());
        return redirect()->route('admin.patrols.index')->with('success', 'Patrulla creada.');
    }
    
    // ... edit, update, destroy (similar lógica) ...
}