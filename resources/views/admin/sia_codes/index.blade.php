@extends('layouts.admin')

@section('title', 'Configuración Códigos SIA')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Diccionario de Eventos SIA</h1>
        <a href="{{ route('admin.sia-codes.create') }}" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-4 rounded transition">
            + Nuevo Código
        </a>
    </div>

    <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-200 uppercase bg-gray-800">
                <tr>
                    <th class="px-6 py-3">Código</th>
                    <th class="px-6 py-3">Descripción</th>
                    <th class="px-6 py-3">Prioridad</th>
                    <th class="px-6 py-3">Color</th>
                    <th class="px-6 py-3">Sonido</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($codes as $code)
                    <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                        <td class="px-6 py-4 font-mono font-bold text-white text-lg">{{ $code->code }}</td>
                        <td class="px-6 py-4">{{ $code->description }}</td>
                        <td class="px-6 py-4">
                            @if($code->priority >= 5) <span class="bg-red-900 text-red-200 px-2 py-1 rounded text-xs font-bold">CRÍTICO (5)</span>
                            @elseif($code->priority == 4) <span class="bg-orange-900 text-orange-200 px-2 py-1 rounded text-xs">ALTA (4)</span>
                            @elseif($code->priority <= 1) <span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs">BAJA ({{$code->priority}})</span>
                            @else <span class="bg-blue-900 text-blue-200 px-2 py-1 rounded text-xs">MEDIA ({{$code->priority}})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded border border-gray-500" style="background-color: {{ $code->color_hex }};"></div>
                                <span class="font-mono text-xs">{{ $code->color_hex }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">{{ $code->sound_alert ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.sia-codes.edit', $code->id) }}" class="text-blue-400 hover:text-white mr-3">Editar</a>
                            <form action="{{ route('admin.sia-codes.destroy', $code->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este código?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-300">Borrar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $codes->links() }}
        </div>
    </div>
@endsection