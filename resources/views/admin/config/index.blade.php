@extends('layouts.admin')
@section('title', 'Configuración de Incidentes')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
        <h3 class="text-white font-bold mb-4 flex items-center gap-2">✅ Resoluciones de Cierre</h3>
        
        <form action="{{ route('config.resolutions.store') }}" method="POST" class="flex gap-2 mb-6">
            @csrf
            <input type="text" name="name" placeholder="Ej: Falsa Alarma (Clima)" class="bg-slate-900 border-slate-600 text-white text-xs p-2 rounded flex-1" required>
            <input type="text" name="code" placeholder="slug_unico" class="bg-slate-900 border-slate-600 text-white text-xs p-2 rounded w-24" required>
            <button class="bg-green-600 text-white px-3 rounded font-bold text-xs">+</button>
        </form>

        <ul class="space-y-2">
            @foreach($resolutions as $res)
            <li class="flex justify-between items-center bg-slate-900 p-2 rounded border border-slate-700">
                <span class="text-slate-300 text-sm">{{ $res->name }}</span>
                <form action="{{ route('config.resolutions.destroy', $res->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:text-white text-xs">🗑️</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
        <h3 class="text-white font-bold mb-4 flex items-center gap-2">⏳ Motivos de Espera</h3>
        
        <form action="{{ route('config.hold-reasons.store') }}" method="POST" class="flex gap-2 mb-6">
            @csrf
            <input type="text" name="name" placeholder="Ej: Esperando Policía" class="bg-slate-900 border-slate-600 text-white text-xs p-2 rounded flex-1" required>
            <input type="text" name="code" placeholder="slug_unico" class="bg-slate-900 border-slate-600 text-white text-xs p-2 rounded w-24" required>
            <button class="bg-yellow-600 text-white px-3 rounded font-bold text-xs">+</button>
        </form>

        <ul class="space-y-2">
            @foreach($holdReasons as $hold)
            <li class="flex justify-between items-center bg-slate-900 p-2 rounded border border-slate-700">
                <span class="text-slate-300 text-sm">{{ $hold->name }}</span>
                <form action="{{ route('config.hold-reasons.destroy', $hold->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:text-white text-xs">🗑️</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection