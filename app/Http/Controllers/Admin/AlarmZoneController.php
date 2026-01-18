<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\AlarmZone;
use Illuminate\Http\Request;

class AlarmZoneController extends Controller
{
    public function store(Request $request, $accountId)
    {
        $account = AlarmAccount::findOrFail($accountId);

        $validated = $request->validate([
            'zone_number' => 'required|string|max:10', // Ej: 001, 01, 1
            'name' => 'required|string|max:100', // Ej: Puerta Principal
            'type' => 'required|string', // Ej: Instantánea, Retardada
            'sensor_type' => 'nullable|string', // Ej: Magnético, PIR, Humo
        ]);

        // Evitar duplicados de número de zona en la misma cuenta
        if ($account->zones()->where('zone_number', $validated['zone_number'])->exists()) {
            return back()->with('error', 'El número de zona ' . $validated['zone_number'] . ' ya existe en este panel.');
        }

        $account->zones()->create($validated);

        return back()->with('success', 'Zona agregada correctamente.');
    }

    public function destroy($id)
    {
        $zone = AlarmZone::findOrFail($id);
        $zone->delete();

        return back()->with('success', 'Zona eliminada.');
    }
}