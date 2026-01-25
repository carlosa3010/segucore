@extends('layouts.admin')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-white">Planes de Facturación</h2>
    <a href="{{ route('admin.config.plans.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">
        + Nuevo Plan
    </a>
</div>

<div class="bg-[#1e293b] rounded-lg border border-gray-700 overflow-hidden">
    <table class="w-full text-sm text-left text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-900/50">
            <tr>
                <th class="px-6 py-3">Nombre</th>
                <th class="px-6 py-3">Base</th>
                <th class="px-6 py-3 text-yellow-400">Precio x GPS</th>
                <th class="px-6 py-3 text-[#C6F211]">Precio x Alarma</th>
                <th class="px-6 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @foreach($plans as $plan)
            <tr class="hover:bg-gray-800/50">
                <td class="px-6 py-4 font-bold text-white">{{ $plan->name }}</td>
                <td class="px-6 py-4">${{ number_format($plan->price, 2) }}</td>
                <td class="px-6 py-4">${{ number_format($plan->gps_price, 2) }}</td>
                <td class="px-6 py-4">${{ number_format($plan->alarm_price, 2) }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('admin.config.plans.edit', $plan->id) }}" class="text-blue-400 hover:text-white">Editar</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection