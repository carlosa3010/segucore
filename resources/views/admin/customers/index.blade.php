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
            <input type="text" name="search" placeholder="Buscar por nombre, cédula o teléfono..." 
                   class="form-input flex-1" value="{{ request('search') }}">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 rounded transition">Buscar</button>
        </form>
    </div>

    <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-200 uppercase bg-gray-800">
                <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3">Identificación</th>
                    <th class="px-6 py-3">Contacto</th>
                    <th class="px-6 py-3">Cuentas (Paneles)</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                    <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                        <td class="px-6 py-4 font-bold text-white">
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </td>
                        <td class="px-6 py-4">{{ $customer->national_id }}</td>
                        <td class="px-6 py-4">
                            <div class="text-xs">{{ $customer->phone_1 }}</div>
                            <div class="text-xs text-gray-500">{{ $customer->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-gray-700 px-2 py-1 rounded text-xs text-white">
                                {{ $customer->accounts_count ?? 0 }} Activas
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-blue-400 hover:text-blue-300 font-medium mr-3">Ver Ficha</a>
                            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="text-gray-500 hover:text-white">Editar</a>
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