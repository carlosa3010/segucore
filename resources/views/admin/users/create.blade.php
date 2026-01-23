@extends('layouts.admin')
@section('title', 'Nuevo Usuario')

@section('content')
<div class="max-w-xl mx-auto bg-slate-800 p-8 rounded-lg border border-slate-700 shadow-xl">
    <h2 class="text-2xl font-bold text-white mb-6">Crear Nuevo Usuario</h2>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm text-slate-400 mb-1">Nombre Completo</label>
            <input type="text" name="name" class="form-input bg-slate-900 border-slate-600 text-white" required value="{{ old('name') }}">
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Correo Electrónico</label>
            <input type="email" name="email" class="form-input bg-slate-900 border-slate-600 text-white" required value="{{ old('email') }}">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1">Contraseña</label>
                <input type="password" name="password" class="form-input bg-slate-900 border-slate-600 text-white" required>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" class="form-input bg-slate-900 border-slate-600 text-white" required>
            </div>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Rol de Usuario</label>
            <select name="role" class="form-input bg-slate-900 border-slate-600 text-white">
                <option value="operator">Operador (Monitoreo)</option>
                <option value="supervisor">Supervisor (Gestión)</option>
                <option value="admin">Administrador (Total)</option>
                <option value="client">Cliente (Solo Lectura)</option>
            </select>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" id="active" checked class="rounded bg-slate-700 border-slate-600 text-blue-600 focus:ring-blue-500">
            <label for="active" class="text-sm text-slate-300">Usuario Activo (Puede iniciar sesión)</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-slate-400 hover:text-white transition">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded transition">
                Guardar Usuario
            </button>
        </div>
    </form>
</div>
@endsection