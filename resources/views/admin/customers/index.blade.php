@extends('layouts.admin')

@section('title', 'Gestión de Clientes')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Cartera de Clientes</h1>
        <a href="{{ route('admin.customers.create') }}" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-4 rounded transition">
            + Nuevo Cliente
        </a>
    </div>

    <div class="bg-[#1e293b] p-4 rounded-lg mb-6 border border-gray-700">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" placeholder="Buscar por nombre, empresa, cédula o teléfono..." 
                   class="form-input flex-1" value="{{ request('search') }}">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 rounded transition">Buscar</button>
        </form>
    </div>

    <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-200 uppercase bg-gray-800">
                <tr>
                    <th class="px-6 py-3">Cliente / Empresa</th>
                    <th class="px-6 py-3">Identificación</th>
                    <th class="px-6 py-3">Contacto</th>
                    <th class="px-6 py-3">Cuentas</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                    <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                        <td class="px-6 py-4 font-bold text-white">
                            {{ $customer->full_name }}
                            @if($customer->type === 'company')
                                <span class="ml-2 text-[10px] bg-blue-900 text-blue-200 px-1.5 py-0.5 rounded border border-blue-700 font-mono">EMP</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4">{{ $customer->national_id }}</td>
                        
                        <td class="px-6 py-4">
                            <div class="text-xs text-white">{{ $customer->phone_1 }}</div>
                            <div class="text-xs text-gray-500">{{ $customer->email }}</div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <span class="bg-gray-800 border border-gray-600 px-2 py-1 rounded text-xs text-gray-300">
                                {{ $customer->accounts->count() }} Activas
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-4">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-blue-400 hover:text-blue-300 font-medium">Ver Ficha</a>
                                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="text-gray-400 hover:text-white">Editar</a>
                                
                                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este cliente? Se borrarán también sus cuentas de alarma asociadas.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 font-medium">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $customers->links() }} 
        </div>
    </div>
@endsection