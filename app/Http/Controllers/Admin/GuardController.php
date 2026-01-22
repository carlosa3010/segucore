<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guard;
use App\Models\User;
use App\Models\Patrol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($request) {
            // 1. Crear Usuario
            $user = User::create([
                'name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'guard'
            ]);

            // 2. Crear Guardia
            Guard::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'badge_number' => $request->badge_number,
                'phone' => $request->phone,
                'current_patrol_id' => $request->patrol_id
            ]);
        });

        return redirect()->route('admin.guards.index')->with('success', 'Guardia registrado correctamente.');
    }

    // --- MÉTODOS AGREGADOS ---

    public function edit($id)
    {
        $guard = Guard::with('user')->findOrFail($id);
        $patrols = Patrol::where('is_active', true)->get();
        
        return view('admin.guards.edit', compact('guard', 'patrols'));
    }

    public function update(Request $request, $id)
    {
        $guard = Guard::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'badge_number' => 'required|string|max:50|unique:guards,badge_number,' . $id,
            'phone' => 'nullable|string|max:20',
            'current_patrol_id' => 'nullable|exists:patrols,id',
        ]);

        // Actualizamos datos del guardia
        $guard->update([
            'full_name' => $request->full_name,
            'badge_number' => $request->badge_number,
            'phone' => $request->phone,
            'current_patrol_id' => $request->current_patrol_id,
            'on_duty' => $request->has('on_duty') // Checkbox
        ]);

        // Actualizamos el nombre en la tabla users también para consistencia
        $guard->user->update(['name' => $request->full_name]);

        return redirect()->route('admin.guards.index')->with('success', 'Perfil actualizado.');
    }

    public function destroy($id)
    {
        $guard = Guard::findOrFail($id);
        
        // Al eliminar el guardia, también eliminamos su usuario de acceso (Cascade en BD o manual)
        // Como definimos onDelete('cascade') en la migración para guards->user_id, 
        // lo correcto es borrar el User y dejar que la BD borre el Guard.
        
        $user = $guard->user;
        if($user) {
            $user->delete(); // Esto borrará al guardia por la cascada
        } else {
            $guard->delete(); // Fallback por si el usuario ya no existe
        }

        return redirect()->route('admin.guards.index')->with('success', 'Guardia eliminado del sistema.');
    }
}