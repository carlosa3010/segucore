<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\AlarmZone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Importante para la regla única

class AlarmZoneController extends Controller
{
    public function store(Request $request, $accountId)
    {
        $account = AlarmAccount::findOrFail($accountId);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'sensor_type' => 'nullable|string',
            'zone_number' => [
                'required', 
                'string', 
                'max:10',
                // VALIDACIÓN COMPUESTA: Único solo para este alarm_account_id
                Rule::unique('alarm_zones')->where(function ($query) use ($accountId) {
                    return $query->where('alarm_account_id', $accountId);
                }),
            ],
        ], [
            'zone_number.unique' => 'Este número de zona ya existe en este panel.'
        ]);

        $zone = $account->zones()->create($validated);

        // AUDITORÍA AUTOMÁTICA
        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note', // Usamos 'note' para cambios de configuración
            'content' => "SISTEMA: Se agregó la Zona #{$validated['zone_number']} ({$validated['name']})."
        ]);

        return back()->with('success', 'Zona agregada correctamente.');
    }

    public function destroy($id)
    {
        $zone = AlarmZone::with('account')->findOrFail($id);
        $account = $zone->account;
        $zoneNumber = $zone->zone_number;
        $zoneName = $zone->name;

        $zone->delete();

        // AUDITORÍA AUTOMÁTICA
        if($account) {
            $account->logs()->create([
                'user_id' => auth()->id() ?? 1,
                'type' => 'note',
                'content' => "SISTEMA: Se eliminó la Zona #{$zoneNumber} ({$zoneName})."
            ]);
        }

        return back()->with('success', 'Zona eliminada.');
    }
}