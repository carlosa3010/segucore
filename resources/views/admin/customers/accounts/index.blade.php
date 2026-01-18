@extends('layouts.admin')

@section('title', 'Cuentas de Monitoreo')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Cuentas de Monitoreo</h1>
        <a href="{{ route('admin.accounts.create') }}" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-4 rounded transition">
            + Nueva Cuenta
        </a>
    </div>

    <div class="bg-[#1e293b] p-4 rounded-lg mb-6 border border-gray-700">
        <form action="{{ route('admin.accounts.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" placeholder="Buscar por abonado, cliente o dirección..." 
                   class="form-input flex-1" value="{{ request('search') }}">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 rounded transition">Buscar</button>
        </form>
    </div>

    <div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-400">
            <thead class="text-xs text-gray-200 uppercase bg-gray-800">
                <tr>
                    <th class="px-6 py-3">Abonado</th>
                    <th class="px-6 py-3">Cliente</th>
                    <th class="px-6 py-3">Ubicación</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $acc)
                    <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                        <td class="px-6 py-4 font-mono font-bold text-white text-lg">
                            {{ $acc->account_number }}
                        </td>
                        <td class="px-6 py-4">
                            @if($acc->customer)
                                <a href="{{ route('admin.customers.show', $acc->customer_id) }}" class="text-blue-400 hover:underline">
                                    {{ $acc->customer->full_name }}
                                </a>
                            @else
                                <span class="text-red-500">Sin Cliente</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-white font-medium">{{ $acc->branch_name ?? 'Principal' }}</div>
                            <div class="text-xs truncate max-w-[200px]" title="{{ $acc->installation_address }}">
                                {{ $acc->installation_address }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($acc->service_status == 'active')
                                <span class="bg-green-900 text-green-200 px-2 py-1 rounded text-xs font-bold border border-green-700">ACTIVO</span>
                            @elseif($acc->service_status == 'suspended')
                                <span class="bg-red-900 text-red-200 px-2 py-1 rounded text-xs font-bold border border-red-700">SUSPENDIDO</span>
                            @else
                                <span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs font-bold">{{ strtoupper($acc->service_status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.accounts.show', $acc->id) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-xs transition border border-gray-600">
                                    Gestionar
                                </a>
                                
                                <form action="{{ route('admin.accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta cuenta de alarma? Se borrará todo su historial y zonas.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-300 p-1">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $accounts->links() }}
        </div>
    </div>
@endsection