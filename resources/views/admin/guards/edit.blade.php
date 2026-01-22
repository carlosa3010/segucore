@extends('layouts.admin')
@section('title', 'Editar Guardia')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-2xl bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Editar Perfil de Guardia</h2>
            <a href="{{ route('admin.guards.index') }}" class="text-xs text-slate-400 hover:text-white">Volver</a>
        </div>
        
        <form action="{{ route('admin.guards.update', $guard->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-500 uppercase border-b border-slate-700 pb-1">Información Personal</h3>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Nombre Completo</label>
                        <input type="text" name="full_name" value="{{ $guard->full_name }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Número de Placa / Carnet</label>
                        <input type="text" name="badge_number" value="{{ $guard->badge_number }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white font-mono" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ $guard->phone }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-500 uppercase border-b border-slate-700 pb-1">Acceso App y Operación</h3>
                    
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Correo Electrónico (Solo Lectura)</label>
                        <input type="email" value="{{ $guard->user->email }}" class="w-full bg-slate-800 border border-slate-700 rounded p-2 text-slate-400 cursor-not-allowed" readonly>
                        <p class="text-[10px] text-slate-500 mt-1">Para cambiar el email, edita el usuario en el módulo de usuarios.</p>
                    </div>

                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Asignación Actual (Patrulla)</label>
                        <select name="current_patrol_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                            <option value="">-- Sin Asignación (Descanso) --</option>
                            @foreach($patrols as $p)
                                <option value="{{ $p->id }}" {{ $guard->current_patrol_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-slate-900 p-3 rounded border border-slate-700 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="on_duty" value="1" {{ $guard->on_duty ? 'checked' : '' }} class="w-4 h-4 rounded bg-slate-700 border-slate-500 text-green-500">
                            <span class="text-sm text-white">Forzar estado "En Turno" (On Duty)</span>
                        </label>
                        <p class="text-[10px] text-slate-500 mt-1 pl-6">
                            Normalmente esto lo activa el guardia desde la App.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-700">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg transition">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection