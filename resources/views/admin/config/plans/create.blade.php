@extends('layouts.admin')
@section('title', 'Nuevo Plan')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Crear Nuevo Plan</h1>
    
    <form action="{{ route('admin.config.plans.store') }}" method="POST" class="bg-[#1e293b] p-8 rounded-lg border border-gray-700">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Nombre del Plan</label>
            <input type="text" name="name" class="form-input" required placeholder="Ej. Corporativo Gold">
        </div>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm text-[#C6F211] font-bold mb-1">Precio Base ($)</label>
                <input type="number" step="0.01" name="price" class="form-input" required>
                <span class="text-xs text-gray-500">Mantenimiento fijo</span>
            </div>
            <div>
                <label class="block text-sm text-blue-400 font-bold mb-1">Tasa x GPS ($)</label>
                <input type="number" step="0.01" name="gps_price" class="form-input" required>
            </div>
            <div>
                <label class="block text-sm text-red-400 font-bold mb-1">Tasa x Alarma ($)</label>
                <input type="number" step="0.01" name="alarm_price" class="form-input" required>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm text-gray-400 mb-1">Descripción</label>
            <textarea name="description" class="form-input" rows="3"></textarea>
        </div>

        <button type="submit" class="w-full bg-[#C6F211] text-black font-bold py-3 rounded hover:bg-[#a3c90d] transition">
            Guardar Plan
        </button>
    </form>
</div>
@endsection