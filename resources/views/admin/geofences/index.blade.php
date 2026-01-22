@extends('layouts.admin')
@section('title', 'Geocercas')

@section('content')
<div class="bg-slate-900 min-h-screen p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">🌐 Geocercas</h1>
        <a href="{{ route('admin.geofences.create') }}" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg font-bold text-sm transition shadow-lg shadow-green-900/50">
            + Nueva Zona
        </a>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden shadow-xl">
        <table class="w-full text-sm text-left text-slate-400">
            <thead class="bg-slate-950 text-slate-200 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3">ID Traccar</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($geofences as $geo)
                <tr class="hover:bg-slate-700/50 transition">
                    <td class="px-4 py-3 font-bold text-white">{{ $geo->name }}</td>
                    <td class="px-4 py-3">{{ Str::limit($geo->description, 50) }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-yellow-500">{{ $geo->traccar_geofence_id ?? 'PENDIENTE' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            
                            <a href="{{ route('admin.geofences.edit', $geo->id) }}" class="bg-slate-700 hover:bg-yellow-600/80 text-yellow-400 hover:text-white p-1.5 rounded transition" title="Editar">
                                ✏️
                            </a>

                            <form action="{{ route('admin.geofences.destroy', $geo->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar la geocerca {{ $geo->name }}? Esto dejará de monitorear la zona.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-slate-700 hover:bg-red-900/80 text-red-400 hover:text-red-200 p-1.5 rounded transition" title="Eliminar">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                
                @if($geofences->isEmpty())
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-500">
                        No hay geocercas registradas.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-700">
            {{ $geofences->links() }}
        </div>
    </div>
</div>
@endsection