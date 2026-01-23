@extends('layouts.admin')
@section('title', 'Resoluciones de Cierre')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="bg-slate-800 p-6 rounded-lg border border-slate-700 h-fit">
        <h2 class="text-xl font-bold text-white mb-4">Nueva Resolución</h2>
        <form action="{{ route('admin.config.resolutions.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs uppercase text-slate-400 mb-1">Nombre de la Resolución</label>
                <input type="text" name="name" class="form-input bg-slate-900 border-slate-600 text-white" placeholder="Ej: Falsa Alarma (Usuario)" required>
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-2 rounded transition">
                Agregar +
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Catálogo de Resoluciones</h3>
            <a href="{{ route('admin.config.hold-reasons.index') }}" class="text-sm text-blue-400 hover:text-blue-300">Ir a Motivos de Espera →</a>
        </div>
        
        <table class="w-full text-sm text-left text-slate-400">
            <thead class="bg-slate-900 text-xs uppercase">
                <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($resolutions as $resolution)
                <tr class="hover:bg-slate-700/50">
                    <td class="px-6 py-4 font-medium text-white">{{ $resolution->name }}</td>
                    <td class="px-6 py-4 text-right">
                        <form action="{{ route('admin.config.resolutions.destroy', $resolution->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400" onclick="return confirm('¿Eliminar?')">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($resolutions->isEmpty())
                    <tr><td colspan="2" class="p-4 text-center italic">No hay resoluciones registradas.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection