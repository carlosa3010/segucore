@extends('layouts.admin')
@section('title', 'Nuevo Guardia')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-2xl bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
        <h2 class="text-xl font-bold text-white mb-6">Registrar Guardia & Usuario App</h2>
        
        <form action="{{ route('admin.guards.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-500 uppercase border-b border-slate-700 pb-1">Información Personal</h3>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Nombre Completo</label>
                        <input type="text" name="full_name" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Número de Placa / Carnet</label>
                        <input type="text" name="badge_number" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white font-mono" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Teléfono</label>
                        <input type="text" name="phone" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-500 uppercase border-b border-slate-700 pb-1">Acceso App Móvil</h3>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Correo Electrónico (Login)</label>
                        <input type="email" name="email" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Contraseña</label>
                        <input type="password" name="password" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Asignar a Patrulla (Opcional)</label>
                        <select name="patrol_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                            <option value="">-- Sin Asignación --</option>
                            @foreach($patrols as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-700">
                <a href="{{ route('admin.guards.index') }}" class="text-slate-400 text-sm py-2 px-4 hover:text-white">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg">Registrar Personal</button>
            </div>
        </form>
    </div>
</div>
@endsection