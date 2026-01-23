<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
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
        $users = User::with('customer')->orderBy('name')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        // CORRECCIÓN SQL: Ordenamos por Razón Social y luego por Nombre
        // Usamos COALESCE para que SQL ordene por el que no sea nulo
        $customers = Customer::orderByRaw('COALESCE(business_name, first_name) ASC')->get();
        
        return view('admin.users.create', compact('customers'));
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
            'customer_id' => ['nullable', 'exists:customers,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'customer_id' => $request->customer_id,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario registrado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(User $user)
    {
        if (Auth::user()->role === 'supervisor' && $user->role === 'admin') {
            return redirect()->route('admin.users.index')->with('error', 'No tienes permisos para editar a un Administrador.');
        }

        // CORRECCIÓN SQL: Igual aquí, ordenamos por columnas reales
        $customers = Customer::orderByRaw('COALESCE(business_name, first_name) ASC')->get();

        return view('admin.users.edit', compact('user', 'customers'));
    }

    /**
     * Actualizar usuario.
     */
    public function update(Request $request, User $user)
    {
        if (Auth::user()->role === 'supervisor' && $user->role === 'admin') {
            return back()->with('error', 'Acción no autorizada sobre un Administrador.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,supervisor,operator,client'],
            'customer_id' => ['nullable', 'exists:customers,id'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->customer_id = $request->customer_id;
        
        if ($user->id === Auth::id()) {
            $user->is_active = true;
        } else {
            $user->is_active = $request->has('is_active');
        }

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
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Por seguridad, no puedes eliminar tu propia cuenta.');
        }

        if (Auth::user()->role === 'supervisor') {
            return back()->with('error', 'Acción denegada: Los supervisores no tienen permiso para eliminar registros.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado del sistema.');
    }

    public function changePasswordView()
    {
        return view('admin.users.change_password');
    }

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