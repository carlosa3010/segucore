@extends('layouts.admin')
@section('title', 'Motivos de Espera')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="bg-slate-800 p-6 rounded-lg border border-slate-700 h-fit">
        <h2 class="text-xl font-bold text-white mb-4">Nuevo Motivo de Espera</h2>
        
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-500/20 border border-red-500 rounded text-red-200 text-sm">
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.config.hold_reasons.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-xs uppercase text-slate-400 mb-1">Razón / Motivo</label>
                <input type="text" name="name" class="w-full bg-slate-900 border border-slate-600 text-white rounded p-2 focus:outline-none focus:border-yellow-500" placeholder="Ej: Esperando arribo policial" value="{{ old('name') }}" required>
            </div>

            <div class="mb-4">
                <label class="block text-xs uppercase text-slate-400 mb-1">Código Interno</label>
                <input type="text" name="code" class="w-full bg-slate-900 border border-slate-600 text-white rounded p-2 focus:outline-none focus:border-yellow-500" placeholder="Ej: WAIT_POLICE" value="{{ old('code') }}" required style="text-transform: uppercase">
                <p class="text-xs text-slate-500 mt-1">Identificador único para el sistema.</p>
            </div>

            <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-500 text-white font-bold py-2 rounded transition">
                Agregar +
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Catálogo de Motivos "On Hold"</h3>
            <a href="{{ route('admin.config.resolutions.index') }}" class="text-sm text-blue-400 hover:text-blue-300">← Volver a Resoluciones</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="bg-slate-900 text-xs uppercase text-slate-300">
                    <tr>
                        <th class="px-6 py-3">Código</th>
                        <th class="px-6 py-3">Motivo</th>
                        <th class="px-6 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @foreach($reasons as $reason)
                    <tr class="hover:bg-slate-700/50 transition">
                        <td class="px-6 py-4 font-mono text-yellow-500">{{ $reason->code }}</td>
                        <td class="px-6 py-4 font-medium text-white">{{ $reason->name }}</td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.config.hold_reasons.destroy', $reason->id) }}" method="POST" class="inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-400 transition p-1" onclick="return confirm('¿Estás seguro de eliminar este motivo?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    
                    @if($reasons->isEmpty())
                        <tr><td colspan="3" class="p-8 text-center italic text-slate-500">No hay motivos registrados aún.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection