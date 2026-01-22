<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ... index y create iguales ...

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,supervisor,operator,client'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado.');
    }

    public function update(Request $request, User $user)
    {
        // Validación de permisos para editar ADMINS
        if ($user->role === 'admin' && Auth::user()->role !== 'admin') {
            return back()->with('error', 'Solo un Administrador puede editar a otro Administrador.');
        }

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->is_active = $request->has('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        // REGLA: Supervisor no puede eliminar
        if (Auth::user()->role === 'supervisor') {
            return back()->with('error', 'Acción no autorizada: Los supervisores no pueden eliminar usuarios.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado.');
    }
    
    // Vista para cambiar MI propia clave
    public function changePasswordView()
    {
        return view('admin.users.change_password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:8',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}