<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Listar usuarios.
     */
    public function index()
    {
        // Excluimos al usuario actual de la lista para evitar bloqueos accidentales en la vista rápida
        // aunque la validación real está en destroy
        $users = User::orderBy('name')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Guardar nuevo usuario.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,supervisor,operator,client'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario registrado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(User $user)
    {
        // REGLA: Un Supervisor no puede editar a un Admin
        if (Auth::user()->role === 'supervisor' && $user->role === 'admin') {
            return redirect()->route('admin.users.index')->with('error', 'No tienes permisos para editar a un Administrador.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Actualizar usuario.
     */
    public function update(Request $request, User $user)
    {
        // REGLA: Un Supervisor no puede editar a un Admin ni promoverse a sí mismo
        if (Auth::user()->role === 'supervisor' && $user->role === 'admin') {
            return back()->with('error', 'Acción no autorizada sobre un Administrador.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,supervisor,operator,client'],
        ]);

        // Actualizar datos básicos
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        
        // Manejo de suspensión (Checkbox)
        // Si el usuario se está editando a sí mismo, impedimos que se desactive
        if ($user->id === Auth::id()) {
            $user->is_active = true;
        } else {
            $user->is_active = $request->has('is_active');
        }

        // Actualizar password solo si se escribe algo
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar usuario.
     */
    public function destroy(User $user)
    {
        // REGLA 1: No puedes eliminar tu propia cuenta
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Por seguridad, no puedes eliminar tu propia cuenta.');
        }

        // REGLA 2: Los Supervisores NO pueden eliminar usuarios, solo suspenderlos
        if (Auth::user()->role === 'supervisor') {
            return back()->with('error', 'Acción denegada: Los supervisores no tienen permiso para eliminar registros. Contacte a un Administrador.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado del sistema.');
    }

    // ==========================================================
    // CAMBIO DE CONTRASEÑA (Perfil Propio)
    // ==========================================================

    /**
     * Vista para cambiar mi propia contraseña.
     */
    public function changePasswordView()
    {
        return view('admin.users.change_password');
    }

    /**
     * Procesar el cambio de contraseña propio.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Tu contraseña ha sido actualizada exitosamente.');
    }
}