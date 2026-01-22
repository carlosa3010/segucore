@extends('layouts.admin')
@section('title', 'Geocercas')

@section('content')
<div class="bg-slate-900 min-h-screen p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">🌐 Geocercas</h1>
        <a href="{{ route('admin.geofences.create') }}" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg font-bold text-sm transition">
            + Nueva Zona
        </a>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden">
        <table class="w-full text-sm text-left text-slate-400">
            <thead class="bg-slate-950 text-slate-200">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3">ID Traccar</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($geofences as $geo)
                <tr>
                    <td class="px-4 py-3 font-bold text-white">{{ $geo->name }}</td>
                    <td class="px-4 py-3">{{ Str::limit($geo->description, 50) }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $geo->traccar_geofence_id ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-xs text-slate-500 italic">Gestión visual próximamente</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $geofences->links() }}
        </div>
    </div>
</div>
@endsection