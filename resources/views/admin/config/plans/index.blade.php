@extends('layouts.admin')
@section('title', 'Planes de Facturación')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">💰 Planes de Servicio</h1>
    <a href="{{ route('admin.config.plans.create') }}" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-4 rounded transition">
        + Nuevo Plan
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 flex flex-col relative overflow-hidden group hover:border-[#C6F211] transition">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition">
            <span class="text-6xl">💎</span>
        </div>
        
        <h3 class="text-xl font-bold text-white mb-2">{{ $plan->name }}</h3>
        <p class="text-3xl font-bold text-[#C6F211] mb-4">${{ number_format($plan->price, 2) }} <span class="text-sm text-gray-400 font-normal">/mes</span></p>
        
        <div class="space-y-2 mb-6 text-sm text-gray-300">
            <div class="flex justify-between border-b border-gray-700 pb-1">
                <span>Tasa GPS:</span>
                <span class="text-white font-mono">${{ $plan->gps_price }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-1">
                <span>Tasa Alarma:</span>
                <span class="text-white font-mono">${{ $plan->alarm_price }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $plan->description }}</p>
        </div>

        <div class="mt-auto flex gap-2">
            <a href="{{ route('admin.config.plans.edit', $plan) }}" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white text-center py-2 rounded text-sm transition">Editar</a>
        </div>
    </div>
    @endforeach
</div>
@endsection