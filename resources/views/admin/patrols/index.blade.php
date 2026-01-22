@extends('layouts.admin')
@section('title', 'Gestión de Patrullas')

@section('content')
<div class="bg-slate-900 min-h-screen p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">🚓 Unidades de Patrulla</h1>
        <a href="{{ route('admin.patrols.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold text-sm transition">
            + Nueva Unidad
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($patrols as $patrol)
        <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 relative overflow-hidden group hover:border-blue-500/50 transition">
            
            <div class="absolute top-2 right-2">
                @if($patrol->is_active)
                    <span class="bg-green-900/30 text-green-400 text-[10px] px-2 py-0.5 rounded border border-green-900">ACTIVA</span>
                @else
                    <span class="bg-red-900/30 text-red-400 text-[10px] px-2 py-0.5 rounded border border-red-900">INACTIVA</span>
                @endif
            </div>

            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-slate-900 flex items-center justify-center text-2xl border border-slate-600">
                    @if($patrol->vehicle_type == 'motorcycle') 🏍️
                    @elseif($patrol->vehicle_type == 'foot') 🚶
                    @else 🚓 @endif
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg leading-tight">{{ $patrol->name }}</h3>
                    <p class="text-xs text-slate-400 font-mono">{{ $patrol->plate_number ?? 'Sin Placa' }}</p>
                </div>
            </div>

            <div class="space-y-2 text-sm">
                <div class="bg-slate-900/50 p-2 rounded flex justify-between items-center">
                    <span class="text-slate-500 text-xs uppercase">GPS:</span>
                    @if($patrol->gpsDevice)
                        <span class="text-green-400 font-mono text-xs truncate max-w-[150px]">{{ $patrol->gpsDevice->name }}</span>
                    @else
                        <span class="text-slate-600 text-xs italic">No asignado</span>
                    @endif
                </div>

                <div class="bg-slate-900/50 p-2 rounded">
                    <span class="text-slate-500 text-xs uppercase block mb-1">Personal Asignado:</span>
                    @if($patrol->guards->count() > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($patrol->guards as $guard)
                                <span class="text-xs bg-slate-700 text-white px-1.5 py-0.5 rounded">{{ $guard->full_name }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-slate-600 text-xs italic">Sin personal</span>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2 pt-3 border-t border-slate-700">
                <a href="{{ route('admin.patrols.edit', $patrol->id) }}" class="text-xs bg-slate-700 hover:bg-yellow-600 text-white px-3 py-1.5 rounded transition">Editar</a>
                <form action="{{ route('admin.patrols.destroy', $patrol->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta patrulla?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs bg-slate-700 hover:bg-red-600 text-white px-3 py-1.5 rounded transition">Eliminar</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection