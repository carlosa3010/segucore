@extends('layouts.admin')
@section('title', 'Editar Usuario')

@section('content')
<div class="max-w-xl mx-auto bg-slate-800 p-8 rounded-lg border border-slate-700 shadow-xl">
    <h2 class="text-2xl font-bold text-white mb-6">Editar Usuario: {{ $user->name }}</h2>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm text-slate-400 mb-1">Nombre Completo</label>
            <input type="text" name="name" class="form-input bg-slate-900 border-slate-600 text-white" required value="{{ old('name', $user->name) }}">
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Correo Electrónico</label>
            <input type="email" name="email" class="form-input bg-slate-900 border-slate-600 text-white" required value="{{ old('email', $user->email) }}">
        </div>

        <div class="bg-slate-900 p-4 rounded border border-slate-700">
            <label class="block text-xs uppercase text-slate-500 mb-2 font-bold">Cambiar Contraseña (Opcional)</label>
            <div class="grid grid-cols-2 gap-4">
                <input type="password" name="password" placeholder="Nueva contraseña" class="form-input bg-slate-800 border-slate-600 text-white text-sm">
                <input type="password" name="password_confirmation" placeholder="Confirmar" class="form-input bg-slate-800 border-slate-600 text-white text-sm">
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Dejar en blanco para mantener la actual.</p>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Rol de Usuario</label>
            <select name="role" class="form-input bg-slate-900 border-slate-600 text-white">
                <option value="operator" {{ $user->role == 'operator' ? 'selected' : '' }}>Operador</option>
                <option value="supervisor" {{ $user->role == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="client" {{ $user->role == 'client' ? 'selected' : '' }}>Cliente</option>
            </select>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" id="active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded bg-slate-700 border-slate-600 text-blue-600 focus:ring-blue-500">
            <label for="active" class="text-sm text-slate-300">Usuario Activo</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-slate-400 hover:text-white transition">Cancelar</a>
            <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded transition">
                Actualizar Usuario
            </button>
        </div>
    </form>
</div>
@endsection