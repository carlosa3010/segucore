<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guard;
use App\Models\User;
use App\Models\Patrol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuardController extends Controller
{
    public function index()
    {
        $guards = Guard::with('patrol', 'user')->get();
        return view('admin.guards.index', compact('guards'));
    }

    public function create()
    {
        $patrols = Patrol::where('is_active', true)->get();
        return view('admin.guards.create', compact('patrols'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'badge_number' => 'required|unique:guards,badge_number',
            'password' => 'required|min:6'
        ]);

        // 1. Crear Usuario para Login en App
        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guard' // Asegúrate de manejar roles o permisos
        ]);

        // 2. Crear Perfil de Guardia
        Guard::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'badge_number' => $request->badge_number,
            'phone' => $request->phone,
            'current_patrol_id' => $request->patrol_id
        ]);

        return redirect()->route('admin.guards.index')->with('success', 'Guardia registrado con acceso a App.');
    }
}