<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\PanelUser;
use Illuminate\Http\Request;

class PanelUserController extends Controller
{
    public function store(Request $request, $accountId)
    {
        $validated = $request->validate([
            'user_number' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'role' => 'required|string'
        ]);

        $account = AlarmAccount::findOrFail($accountId);
        $account->panelUsers()->create($validated);

        return back()->with('success', 'Usuario de panel agregado.');
    }

    public function destroy($id)
    {
        $user = PanelUser::findOrFail($id);
        $user->delete();
        return back()->with('success', 'Usuario eliminado.');
    }
}