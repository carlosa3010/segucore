<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncidentResolution;
use App\Models\IncidentHoldReason;
use Illuminate\Http\Request;

class IncidentConfigController extends Controller
{
    // --- RESOLUCIONES ---
    public function indexResolutions()
    {
        $resolutions = IncidentResolution::all();
        return view('admin.config.resolutions.index', compact('resolutions'));
    }

    public function storeResolution(Request $request)
    {
        $request->validate(['name' => 'required', 'code' => 'required|unique:incident_resolutions']);
        IncidentResolution::create($request->all());
        return back()->with('success', 'Resolución creada.');
    }

    public function destroyResolution($id)
    {
        IncidentResolution::destroy($id);
        return back()->with('success', 'Eliminado.');
    }

    // --- MOTIVOS DE ESPERA ---
    public function indexHoldReasons()
    {
        $reasons = IncidentHoldReason::all();
        return view('admin.config.hold_reasons.index', compact('reasons'));
    }

    public function storeHoldReason(Request $request)
    {
        $request->validate(['name' => 'required', 'code' => 'required|unique:incident_hold_reasons']);
        IncidentHoldReason::create($request->all());
        return back()->with('success', 'Motivo creado.');
    }

    public function destroyHoldReason($id)
    {
        IncidentHoldReason::destroy($id);
        return back()->with('success', 'Eliminado.');
    }
}