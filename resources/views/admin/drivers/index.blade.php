@extends('layouts.admin')
@section('title', 'Conductores')

@section('content')
<div class="bg-slate-900 min-h-screen p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">👮 Gestión de Conductores</h1>
        <a href="{{ route('admin.drivers.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold text-sm transition">
            + Nuevo Conductor
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($drivers as $driver)
        <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 flex items-center gap-4 hover:bg-slate-750 transition">
            <div class="w-16 h-16 rounded-full bg-slate-700 flex-shrink-0 overflow-hidden border-2 border-slate-600">
                @if($driver->photo_path)
                    <img src="{{ Storage::url($driver->photo_path) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-2xl">👤</div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-white font-bold truncate">{{ $driver->full_name }}</h3>
                <p class="text-xs text-slate-400">Licencia: <span class="font-mono text-yellow-500">{{ $driver->license_number }}</span></p>
                <div class="mt-2 flex gap-2">
                    <a href="{{ route('admin.drivers.edit', $driver->id) }}" class="text-xs bg-slate-700 hover:bg-blue-600 text-white px-2 py-1 rounded transition">Editar</a>
                    <form action="{{ route('admin.drivers.destroy', $driver->id) }}" method="POST" onsubmit="return confirm('¿Eliminar conductor?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs bg-slate-700 hover:bg-red-600 text-white px-2 py-1 rounded transition">Eliminar</button>
                    </form>
                </div>
            </div>
            <div class="text-right">
                <span class="block text-2xl font-bold text-blue-400">{{ $driver->devices_count }}</span>
                <span class="text-[10px] text-slate-500 uppercase">Vehículos</span>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="mt-4">
        {{ $drivers->links() }}
    </div>
</div>
@endsection