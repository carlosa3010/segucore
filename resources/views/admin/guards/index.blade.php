@extends('layouts.admin')
@section('title', 'Personal de Guardia')

@section('content')
<div class="bg-slate-900 min-h-screen p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">👮 Personal de Seguridad</h1>
        <a href="{{ route('admin.guards.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-bold text-sm transition">
            + Nuevo Guardia
        </a>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden shadow-xl">
        <table class="w-full text-sm text-left text-slate-400">
            <thead class="bg-slate-950 text-slate-200 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Credenciales App</th>
                    <th class="px-4 py-3">Placa/ID</th>
                    <th class="px-4 py-3">Asignación</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($guards as $guard)
                <tr class="hover:bg-slate-700/50 transition">
                    <td class="px-4 py-3">
                        <span class="block text-white font-bold">{{ $guard->full_name }}</span>
                        <span class="text-xs">{{ $guard->phone }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs text-blue-400">{{ $guard->user->email }}</span>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $guard->badge_number }}</td>
                    <td class="px-4 py-3">
                        @if($guard->patrol)
                            <span class="bg-slate-700 text-white px-2 py-1 rounded text-xs">🚓 {{ $guard->patrol->name }}</span>
                        @else
                            <span class="text-slate-600 italic text-xs">Sin asignar</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($guard->on_duty)
                            <span class="inline-flex items-center gap-1 bg-green-900/30 text-green-400 border border-green-900 px-2 py-0.5 rounded text-[10px] font-bold animate-pulse">
                                ● EN TURNO
                            </span>
                        @else
                            <span class="text-slate-500 text-[10px]">DESCANSO</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button class="text-slate-500 hover:text-white" title="Editar (Próximamente)">✏️</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($guards->isEmpty())
            <div class="p-8 text-center text-slate-500">No hay guardias registrados.</div>
        @endif
    </div>
</div>
@endsection