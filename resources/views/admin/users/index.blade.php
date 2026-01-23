@extends('layouts.admin')
@section('title', 'Gestión de Usuarios')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">🛡️ Usuarios y Roles</h1>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded transition">
            + Nuevo Usuario
        </a>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-hidden shadow-xl">
        <table class="w-full text-sm text-left text-slate-400">
            <thead class="bg-slate-900 text-xs uppercase text-slate-300">
                <tr>
                    <th class="px-6 py-3">Usuario</th>
                    <th class="px-6 py-3">Rol</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3">Registro</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @foreach($users as $user)
                <tr class="hover:bg-slate-700/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center text-white font-bold uppercase">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-white">{{ $user->name }}</div>
                                <div class="text-xs">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $roleColors = [
                                'admin' => 'bg-red-900/50 text-red-300 border-red-700',
                                'supervisor' => 'bg-orange-900/50 text-orange-300 border-orange-700',
                                'operator' => 'bg-blue-900/50 text-blue-300 border-blue-700',
                                'client' => 'bg-slate-700 text-slate-300 border-slate-600'
                            ];
                            $roleLabel = [
                                'admin' => 'Administrador',
                                'supervisor' => 'Supervisor',
                                'operator' => 'Operador',
                                'client' => 'Cliente'
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs border {{ $roleColors[$user->role] ?? 'bg-gray-700' }}">
                            {{ $roleLabel[$user->role] ?? ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->is_active)
                            <span class="text-green-400 text-xs flex items-center gap-1">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Activo
                            </span>
                        @else
                            <span class="text-red-400 text-xs flex items-center gap-1">
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span> Suspendido
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-400 hover:text-white transition">Editar</a>
                        
                        @if(Auth::id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 transition" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection