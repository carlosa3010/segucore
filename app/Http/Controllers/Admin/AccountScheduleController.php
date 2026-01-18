<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\AccountSchedule;
use Illuminate\Http\Request;

class AccountScheduleController extends Controller
{
    // Guardar Horario Temporal (Vacaciones, Festivos)
    public function storeTemp(Request $request, $accountId)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'valid_until' => 'required|date'
        ]);

        $account = AlarmAccount::findOrFail($accountId);
        
        $account->schedules()->create([
            'type' => 'temporary',
            'day_of_week' => 0, // 0 = comodín para temporal
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'reason' => $request->reason,
            'valid_until' => $request->valid_until
        ]);

        return back()->with('success', 'Horario temporal creado.');
    }

    public function destroy($id)
    {
        $schedule = AccountSchedule::findOrFail($id);
        $schedule->delete();
        return back()->with('success', 'Horario eliminado.');
    }
}