@extends('layouts.admin')
@section('title', 'Cambiar Contraseña')

@section('content')
<div class="max-w-md mx-auto bg-slate-800 p-6 rounded-lg border border-slate-700 mt-10">
    <h2 class="text-xl font-bold text-white mb-6">🔒 Cambiar mi Contraseña</h2>
    
    <form action="{{ route('admin.profile.password.update') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="text-xs text-slate-400 uppercase">Contraseña Actual</label>
                <input type="password" name="current_password" class="form-input" required>
            </div>
            <div>
                <label class="text-xs text-slate-400 uppercase">Nueva Contraseña</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <div>
                <label class="text-xs text-slate-400 uppercase">Confirmar Nueva</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded transition">
                Actualizar Clave
            </button>
        </div>
    </form>
</div>
@endsection